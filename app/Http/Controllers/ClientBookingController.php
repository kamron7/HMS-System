<?php

namespace App\Http\Controllers;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Mail\GuestBookingConfirmed;
use App\Models\Booking;
use App\Models\BookingInquiry;
use App\Models\Guest;
use App\Models\PromoCode;
use App\Models\RoomType;
use App\Services\PricingService;
use App\Services\RoomAvailabilityService;
use App\Services\NotificationService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ClientBookingController extends Controller
{
    public function __construct(
        private RoomAvailabilityService $availability,
        private PricingService          $pricing,
        private TelegramService         $telegram,
    ) {}

    public function index()
    {
        return view('book.index');
    }

    public function rooms(Request $request)
    {
        $data = $request->validate([
            'check_in'  => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'adults'    => 'nullable|integer|min:1|max:10',
        ]);

        $checkIn  = $data['check_in'];
        $checkOut = $data['check_out'];
        $adults   = $data['adults'] ?? 1;
        $nights   = now()->parse($checkIn)->diffInDays(now()->parse($checkOut));

        $roomTypes = RoomType::with('rooms')->get();

        $result = $roomTypes->map(function (RoomType $type) use ($checkIn, $checkOut, $adults, $nights) {
            $available       = $this->availability->availableRooms($type, $checkIn, $checkOut);
            $pricePerNight   = $this->pricing->adjustedPrice($type, $checkIn, $checkOut);
            $pricingBanner   = $this->pricing->activeBanner($type, $checkIn, $checkOut);

            // Collect images from available rooms of this type (deduplicated, capped at 10)
            $images = $type->rooms->flatMap(fn($r) => $r->imageUrls())->unique()->values()->take(10)->all();

            return [
                'id'             => $type->id,
                'name'           => $type->name,
                'capacity'       => $type->capacity,
                'base_price'     => (float) $type->base_price,
                'price_per_night'=> $pricePerNight,
                'total_price'    => $pricePerNight * $nights,
                'nights'         => $nights,
                'amenities'      => $type->amenities ?? [],
                'description'    => $type->description,
                'available'      => $available->count() > 0,
                'pricing_banner' => $pricingBanner,
                'images'         => $images,
            ];
        })->filter(fn($t) => $t['available'] && $t['capacity'] >= $adults)->values();

        return response()->json($result);
    }

    public function checkPromo(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|max:50']);

        $promo = PromoCode::where('code', strtoupper(trim($request->code)))->first();

        if (! $promo || ! $promo->isValid()) {
            return response()->json(['valid' => false, 'message' => 'Промокод недействителен или истёк.']);
        }

        return response()->json([
            'valid'            => true,
            'discount_percent' => (float) $promo->discount_percent,
            'message'          => "Скидка {$promo->discount_percent}% применена!",
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in'     => 'required|date|after_or_equal:today',
            'check_out'    => 'required|date|after:check_in',
            'adults'       => 'required|integer|min:1|max:10',
            'children'     => 'nullable|integer|min:0|max:10',
            'first_name'   => 'required|string|max:80',
            'last_name'    => 'required|string|max:80',
            'phone'        => 'required|string|max:30',
            'email'        => 'nullable|email|max:150',
            'notes'        => 'nullable|string|max:500',
            'promo_code'   => 'nullable|string|max:50',
        ]);

        $roomType = RoomType::findOrFail($data['room_type_id']);
        $checkIn  = $data['check_in'];
        $checkOut = $data['check_out'];
        $nights   = now()->parse($checkIn)->diffInDays(now()->parse($checkOut));

        $booking = DB::transaction(function () use ($data, $roomType, $checkIn, $checkOut, $nights) {
            // Pick first available room with lock
            $available = $this->availability->availableRooms($roomType, $checkIn, $checkOut);
            if ($available->isEmpty()) {
                throw ValidationException::withMessages([
                    'room_type_id' => 'К сожалению, выбранный тип номера уже недоступен на эти даты.',
                ]);
            }
            $room = $available->first();

            // Create or update guest by phone (always apply submitted name)
            $guest = Guest::updateOrCreate(
                ['phone' => $data['phone']],
                [
                    'first_name' => $data['first_name'],
                    'last_name'  => $data['last_name'],
                    'email'      => $data['email'] ?? null,
                ]
            );

            $pricePerNight = $this->pricing->adjustedPrice($roomType, $checkIn, $checkOut);
            $baseTotal     = $pricePerNight * $nights;

            // Apply promo code if provided
            $appliedPromoCode = null;
            $discountAmount   = null;

            if (! empty($data['promo_code'])) {
                $promo = PromoCode::where('code', strtoupper(trim($data['promo_code'])))->lockForUpdate()->first();
                if ($promo && $promo->isValid()) {
                    $discountAmount   = round($baseTotal * $promo->discount_percent / 100, 2);
                    $appliedPromoCode = $promo->code;
                    DB::table('promo_codes')->where('id', $promo->id)->increment('uses_count');
                }
            }

            $totalPrice = $baseTotal - ($discountAmount ?? 0);

            $booking = Booking::create([
                'room_id'            => $room->id,
                'guest_id'           => $guest->id,
                'check_in_date'      => $checkIn,
                'check_out_date'     => $checkOut,
                'adults'             => $data['adults'],
                'children'           => $data['children'] ?? 0,
                'status'             => BookingStatus::Inquiry->value,
                'source'             => BookingSource::Client->value,
                'total_price'        => $totalPrice,
                'notes'              => $data['notes'] ?? null,
                'applied_promo_code' => $appliedPromoCode,
                'discount_amount'    => $discountAmount,
                'created_by'         => 1, // system user fallback; will be replaced in acceptInquiry
            ]);

            BookingInquiry::create([
                'booking_id' => $booking->id,
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'phone'      => $data['phone'],
                'email'      => $data['email'] ?? null,
            ]);

            return $booking;
        });

        // In-app notifications for staff
        $booking->load(['guest', 'room']);
        app(NotificationService::class)->notifyNewInquiry($booking);

        // Notify managers/owner via Telegram
        $name = trim($data['first_name'] . ' ' . $data['last_name']);
        $this->telegram->sendTyped('booking_inquiry', ['owner', 'manager'],
            "📋 <b>Новый запрос от клиента</b>\n" .
            "Гость: {$name}\n" .
            "Телефон: {$data['phone']}\n" .
            "Тип: {$roomType->name}\n" .
            "Заезд: {$checkIn} — {$checkOut}"
        );

        // Send confirmation email if guest has an address
        if ($booking->guest?->email) {
            try {
                Mail::to($booking->guest->email)->send(new GuestBookingConfirmed($booking->load('room.roomType')));
            } catch (\Throwable) {
                // Non-fatal: don't fail the booking if mail is misconfigured
            }
        }

        return redirect()->route('book.confirmed', ['ref' => $booking->id]);
    }

    public function confirmed(int $ref)
    {
        $booking = Booking::with(['room.roomType', 'inquiry'])->findOrFail($ref);
        // Only show confirmation for inquiry/pending bookings from client source
        abort_unless($booking->source === BookingSource::Client, 404);

        return view('book.confirmed', compact('booking'));
    }
}
