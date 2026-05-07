<?php

namespace App\Http\Controllers;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Mail\GuestBookingConfirmed;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\BookingCharge;
use App\Models\Guest;
use App\Models\PromoCode;
use App\Models\Room;
use App\Services\BookingTotalsService;
use App\Services\NotificationService;
use App\Services\PricingService;
use App\Services\RoomAvailabilityService;
use App\Services\TelegramService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private RoomAvailabilityService $availability,
        private BookingTotalsService    $totals,
        private PricingService          $pricing,
    ) {}

    public function timeline(Request $request): View
    {
        $start    = Carbon::parse($request->get('start', today()->toDateString()))->startOfDay();
        $days     = 14;
        $boundary = $start->copy()->addDays($days);
        $cellW    = 52;

        $dates = collect(range(0, $days - 1))->map(fn($i) => $start->copy()->addDays($i));

        $rooms = \App\Models\Room::with('roomType')->orderBy('floor')->orderBy('number')->get();

        $bookings = Booking::with('guest')
            ->whereNotIn('status', [BookingStatus::Cancelled->value, BookingStatus::NoShow->value])
            ->where('check_out_date', '>', $start)
            ->where('check_in_date', '<', $boundary)
            ->get()
            ->groupBy('room_id');

        return view('bookings.timeline', compact('rooms', 'bookings', 'start', 'boundary', 'days', 'cellW', 'dates'));
    }

    public function index(Request $request): View
    {
        $showTrashed = $request->query('trashed') && in_array(auth()->user()->role->value, ['owner', 'manager']);

        $query = $showTrashed
            ? Booking::onlyTrashed()->with(['guest', 'room.roomType', 'payments', 'bookingGroup', 'creator'])->orderBy('deleted_at', 'desc')
            : Booking::with(['guest', 'room.roomType', 'payments', 'bookingGroup', 'creator'])->orderBy('check_in_date', 'desc');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->whereHas('guest', function ($q) use ($search) {
                $q->whereRaw('first_name ILIKE ?', ["%{$search}%"])
                  ->orWhereRaw('last_name ILIKE ?', ["%{$search}%"])
                  ->orWhereRaw('phone ILIKE ?', ["%{$search}%"]);
            });
        }

        if ($checkIn = $request->query('check_in')) {
            $query->where('check_in_date', '>=', $checkIn);
        }

        if ($checkOut = $request->query('check_out')) {
            $query->where('check_out_date', '<=', $checkOut);
        }

        if ($room = $request->query('room')) {
            $query->whereHas('room', fn($q) => $q->whereRaw('number ILIKE ?', ["%{$room}%"]));
        }

        if ($groupFilter = $request->query('group_filter')) {
            if ($groupFilter === 'group') {
                $query->whereNotNull('booking_group_id');
            } elseif ($groupFilter === 'individual') {
                $query->whereNull('booking_group_id');
            }
        }

        $bookings     = $query->paginate(20)->appends($request->query());
        $statuses     = BookingStatus::cases();
        $statusCounts = Booking::selectRaw('status, count(*) as cnt')->groupBy('status')->pluck('cnt', 'status');
        $inquiryCount = (int) ($statusCounts[BookingStatus::Inquiry->value] ?? 0);

        return view('bookings.index', [
            'bookings'     => $bookings,
            'statuses'     => $statuses,
            'statusCounts' => $statusCounts,
            'inquiryCount' => $inquiryCount,
            'showTrashed'  => $showTrashed,
            'search'       => $request->query('search', ''),
            'status'       => $request->query('status', ''),
            'check_in'     => $request->query('check_in', ''),
            'check_out'    => $request->query('check_out', ''),
            'room'         => $request->query('room', ''),
            'group_filter' => $request->query('group_filter', ''),
        ]);
    }

    public function bulkStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'booking_ids'   => ['required', 'array', 'min:1', 'max:100'],
            'booking_ids.*' => ['integer', 'exists:bookings,id'],
            'transition'    => ['required', 'string', Rule::in(['confirmed', 'cancelled'])],
        ]);

        $transition = $request->transition;
        $updated    = 0;

        Booking::whereIn('id', $request->booking_ids)->each(function (Booking $booking) use ($transition, &$updated) {
            $allowed = array_map(fn($s) => $s->value, $booking->status->allowedTransitions());
            if (in_array($transition, $allowed)) {
                $booking->update(['status' => $transition]);
                $updated++;
            }
        });

        return redirect()->back()->with('success', "Обновлено бронирований: {$updated}.");
    }

    public function show(Booking $booking): View
    {
        $booking->load([
            'guest',
            'room.roomType',
            'payments' => fn($q) => $q->orderBy('paid_at'),
            'charges'  => fn($q) => $q->orderBy('created_at'),
            'inquiry',
            'creator',
            'maintenanceRequests' => fn($q) => $q->orderByDesc('created_at'),
            'serviceRequests'     => fn($q) => $q->orderBy('created_at'),
        ]);

        $totals = [
            'room_cost'    => (float) $booking->total_price,
            'charges'      => $this->totals->chargesTotal($booking),
            'grand_total'  => $this->totals->grandTotal($booking),
            'paid'         => $this->totals->paidAmount($booking),
            'deposit'      => $this->totals->depositAmount($booking),
            'balance_due'  => $this->totals->balanceDue($booking),
        ];

        $categories    = BookingCharge::categories();
        $paymentStatus = $this->totals->paymentStatus($booking);
        $guests        = Guest::orderBy('last_name')->get();

        return view('bookings.show', compact('booking', 'totals', 'categories', 'paymentStatus', 'guests'));
    }

    public function create(Request $request): View
    {
        $roomId        = $request->query('room_id') ?? old('room_id');
        $prefilledRoom = $roomId ? Room::with('roomType')->find($roomId) : null;

        $guestId      = $request->query('guest_id');
        $foundGuest   = $guestId ? \App\Models\Guest::find($guestId) : null;
        $prefilledGuests = $foundGuest ? [[
            'id'        => $foundGuest->id,
            'full_name' => $foundGuest->full_name,
            'phone'     => $foundGuest->phone,
            'tag'       => $foundGuest->tag?->value,
            'tag_label' => $foundGuest->tag?->label(),
        ]] : [];

        return view('bookings.create', [
            'prefilledRoomId'   => $request->query('room_id'),
            'prefilledCheckIn'  => $request->query('check_in'),
            'prefilledCheckOut' => $request->query('check_out'),
            'prefilledRoom'     => $prefilledRoom,
            'prefilledGuests'   => $prefilledGuests,
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $booking = DB::transaction(function () use ($validated) {
            $room = Room::lockForUpdate()->findOrFail($validated['room_id']);

            if (! $room->status->canBook()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'room_id' => "Номер №{$room->number} недоступен для бронирования (статус: {$room->status->label()}).",
                ]);
            }

            if (! $this->availability->isAvailable($room, $validated['check_in_date'], $validated['check_out_date'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'room_id' => "Номер №{$room->number} уже занят на выбранные даты ({$validated['check_in_date']} — {$validated['check_out_date']}). Выберите другой номер или измените даты.",
                ]);
            }

            $room->load('roomType');
            $nights        = Carbon::parse($validated['check_in_date'])->diffInDays($validated['check_out_date']);
            $pricePerNight = $this->pricing->adjustedPrice($room->roomType, $validated['check_in_date'], $validated['check_out_date']);
            $baseTotal     = $nights * $pricePerNight;

            $appliedPromoCode = null;
            $discountAmount   = null;

            if (! empty($validated['promo_code'])) {
                $promo = PromoCode::where('code', strtoupper(trim($validated['promo_code'])))->lockForUpdate()->first();
                if ($promo && $promo->isValid($room->room_type_id)) {
                    $discountAmount   = round($baseTotal * $promo->discount_percent / 100, 2);
                    $appliedPromoCode = $promo->code;
                    DB::table('promo_codes')->where('id', $promo->id)->increment('uses_count');
                }
            }

            $totalPrice = $baseTotal - ($discountAmount ?? 0);
            $guestIds   = $validated['guest_ids'];
            $primaryId  = $guestIds[0];

            $booking = Booking::create([
                'room_id'            => $validated['room_id'],
                'guest_id'           => $primaryId,
                'check_in_date'      => $validated['check_in_date'],
                'check_in_time'      => $validated['check_in_time'] ?? null,
                'check_out_date'     => $validated['check_out_date'],
                'check_out_time'     => $validated['check_out_time'] ?? null,
                'adults'             => $validated['adults'],
                'children'           => $validated['children'] ?? 0,
                'notes'              => $validated['notes'] ?? null,
                'status'             => BookingStatus::Pending->value,
                'source'             => BookingSource::Staff->value,
                'total_price'        => $totalPrice,
                'applied_promo_code' => $appliedPromoCode,
                'discount_amount'    => $discountAmount,
                'created_by'         => Auth::id(),
            ]);

            // Sync all guests to pivot, marking primary
            $sync = [];
            foreach ($guestIds as $i => $gid) {
                $sync[$gid] = ['is_primary' => $i === 0];
            }
            $booking->guests()->sync($sync);

            return $booking;
        });

        $booking->load(['guest', 'room.roomType']);
        $checkIn  = Carbon::parse($booking->check_in_date)->format('d.m.Y');
        $checkOut = Carbon::parse($booking->check_out_date)->format('d.m.Y');
        app(TelegramService::class)->sendTyped('booking_new', ['owner', 'manager'],
            "📅 <b>Новое бронирование #{$booking->id}</b>\n" .
            "Гость: {$booking->guest->full_name}\n" .
            "Номер: {$booking->room->number} ({$booking->room->roomType->name})\n" .
            "Заезд: {$checkIn} — {$checkOut}"
        );

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Бронирование создано.');
    }

    public function edit(Booking $booking): RedirectResponse|View
    {
        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::Confirmed])) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Нельзя редактировать бронирование в текущем статусе.');
        }

        $booking->load(['guest', 'room.roomType']);

        return view('bookings.edit', compact('booking'));
    }

    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::Confirmed])) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Нельзя редактировать бронирование в текущем статусе.');
        }

        $validated = $request->validated();
        $room      = Room::findOrFail($validated['room_id']);

        if (! $this->availability->isAvailable($room, $validated['check_in_date'], $validated['check_out_date'], $booking->id)) {
            return back()->withErrors(['check_in_date' => 'Номер занят на выбранные даты.'])->withInput();
        }

        $room->load('roomType');
        $nights        = Carbon::parse($validated['check_in_date'])->diffInDays($validated['check_out_date']);
        $pricePerNight = $this->pricing->adjustedPrice($room->roomType, $validated['check_in_date'], $validated['check_out_date']);
        $totalPrice    = $nights * $pricePerNight;

        $booking->update([
            ...$validated,
            'children'    => $validated['children'] ?? 0,
            'total_price' => $totalPrice,
        ]);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Бронирование обновлено.');
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse|JsonResponse
    {
        $request->validate([
            'transition' => ['required', 'string', Rule::in(array_column(BookingStatus::cases(), 'value'))],
        ]);

        $target = BookingStatus::from($request->transition);

        if (! $booking->status->canTransitionTo($target)) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Недопустимый переход статуса.'], 422);
            }
            return back()->with('error', 'Недопустимый переход статуса.');
        }

        // Block check-in if room is not ready
        if ($target === BookingStatus::CheckedIn) {
            $room = $booking->room ?? $booking->load('room')->room;
            if (! $room->status->canCheckIn()) {
                if ($request->wantsJson()) {
                    return response()->json(['error' => "Номер {$room->number} не готов к заселению (статус: {$room->status->label()})."], 422);
                }
                return back()->with('error', "Номер {$room->number} не готов к заселению (статус: {$room->status->label()}).");
            }
        }

        $booking->status = $target;

        if ($target === BookingStatus::CheckedIn && ! $booking->actual_check_in_at) {
            $booking->actual_check_in_at = now();
        }
        if ($target === BookingStatus::CheckedOut && ! $booking->actual_check_out_at) {
            $booking->actual_check_out_at = now();
        }

        $booking->save();

        $room = $booking->room ?? $booking->load('room')->room;
        match($target) {
            BookingStatus::Pending,
            BookingStatus::Confirmed,
            BookingStatus::CheckedIn  => $room->update(['status' => RoomStatus::Occupied->value]),
            BookingStatus::CheckedOut => $room->update(['status' => RoomStatus::Dirty->value]),
            BookingStatus::Cancelled,
            BookingStatus::NoShow     => $room->update(['status' => RoomStatus::Available->value]),
            default => null,
        };

        // Fire notifications for key transitions
        $booking->load(['guest', 'room']);
        $notifService = app(NotificationService::class);
        if ($target === BookingStatus::Confirmed) {
            $notifService->notifyBookingConfirmed($booking);
        } elseif ($target === BookingStatus::CheckedOut) {
            $notifService->notifyCheckedOut($booking);
        }

        $tg = app(TelegramService::class);
        $guestName = $booking->guest->full_name;
        $roomNum   = $booking->room->number;
        match($target) {
            BookingStatus::Confirmed  => $tg->sendTyped('booking_confirmed', ['owner', 'manager'],
                "✔️ <b>Бронирование подтверждено #{$booking->id}</b>\n" .
                "Гость: {$guestName}\nНомер: {$roomNum}"
            ),
            BookingStatus::CheckedIn  => $tg->sendTyped('booking_checkin', ['owner', 'manager', 'receptionist'],
                "✅ <b>Заселение #{$booking->id}</b>\n" .
                "Гость: {$guestName}\nНомер: {$roomNum}"
            ),
            BookingStatus::CheckedOut => $tg->sendTyped('booking_checkout', ['owner', 'manager', 'receptionist'],
                "🔑 <b>Выселение #{$booking->id}</b>\n" .
                "Гость: {$guestName}\nНомер: {$roomNum}"
            ),
            BookingStatus::Cancelled  => $tg->sendTyped('booking_cancelled', ['owner', 'manager'],
                "❌ <b>Отмена бронирования #{$booking->id}</b>\n" .
                "Гость: {$guestName}\nНомер: {$roomNum}"
            ),
            BookingStatus::NoShow     => $tg->sendTyped('booking_cancelled', ['owner', 'manager'],
                "⚠️ <b>Не явился #{$booking->id}</b>\n" .
                "Гость: {$guestName}\nНомер: {$roomNum}"
            ),
            default => null,
        };

        if ($request->wantsJson()) {
            return response()->json(['status' => $target->value, 'label' => $target->label()]);
        }

        return back()->with('success', "Статус обновлён: {$target->label()}");
    }

    public function acceptInquiry(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->status === BookingStatus::Inquiry, 422);

        $request->validate([
            'action'   => ['required', 'in:existing,new'],
            'guest_id' => ['required_if:action,existing', 'nullable', 'integer', 'exists:guests,id'],
            // new guest fields
            'first_name' => ['required_if:action,new', 'nullable', 'string', 'max:80'],
            'last_name'  => ['required_if:action,new', 'nullable', 'string', 'max:80'],
            'phone'      => ['required_if:action,new', 'nullable', 'string', 'max:30'],
            'email'      => ['nullable', 'email', 'max:150'],
        ]);

        DB::transaction(function () use ($request, $booking) {
            $room = Room::findOrFail($booking->room_id);

            if (! $this->availability->isAvailableLocked(
                $room,
                $booking->check_in_date->toDateString(),
                $booking->check_out_date->toDateString(),
                $booking->id
            )) {
                abort(409, 'Этот номер уже занят на выбранные даты — попробуйте другой номер.');
            }

            if ($request->action === 'existing') {
                $guestId = $request->guest_id;
            } else {
                $guest   = Guest::firstOrCreate(
                    ['phone' => $request->phone],
                    [
                        'first_name' => $request->first_name,
                        'last_name'  => $request->last_name,
                        'email'      => $request->email,
                    ]
                );
                $guestId = $guest->id;
            }

            $booking->update([
                'guest_id' => $guestId,
                'status'   => BookingStatus::Pending->value,
            ]);

            $room->update(['status' => RoomStatus::Occupied->value]);
        });

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Запрос принят, бронирование создано.');
    }

    public function rejectInquiry(Booking $booking): RedirectResponse
    {
        abort_unless($booking->status === BookingStatus::Inquiry, 422);

        $guestId = $booking->guest_id;

        $booking->delete();

        // Delete the auto-created guest if they have no other bookings
        $guest = Guest::find($guestId);
        if ($guest && $guest->bookings()->doesntExist()) {
            $guest->delete();
        }

        return redirect()->route('bookings.index')
            ->with('success', 'Запрос отклонён.');
    }

    public function moveRoom(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
        ]);

        $newRoom = Room::findOrFail($request->room_id);

        // Same room — no-op
        if ($newRoom->id === $booking->room_id) {
            return response()->json(['error' => 'Гость уже в этом номере.'], 422);
        }

        $conflict = DB::transaction(function () use ($booking, $newRoom) {
            return ! $this->availability->isAvailableLocked(
                $newRoom,
                $booking->check_in_date->toDateString(),
                $booking->check_out_date->toDateString(),
                $booking->id
            );
        });

        if ($conflict) {
            return response()->json(['error' => 'Номер занят на эти даты.'], 409);
        }

        $newRoom->load('roomType');
        $nights     = $booking->check_in_date->diffInDays($booking->check_out_date);
        $totalPrice = $nights * $newRoom->roomType->base_price;

        // If checked-in: mark old room as dirty, new room as occupied
        if ($booking->status === BookingStatus::CheckedIn) {
            $oldRoom = $booking->room;
            $oldRoom?->update(['status' => RoomStatus::Dirty->value]);
            $newRoom->update(['status' => RoomStatus::Occupied->value]);
        }

        $booking->update([
            'room_id'     => $newRoom->id,
            'total_price' => $totalPrice,
        ]);

        return response()->json(['ok' => true, 'room_number' => $newRoom->number]);
    }

    public function moveDates(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'check_in_date'  => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
        ]);

        $allowedStatuses = [BookingStatus::Inquiry, BookingStatus::Pending, BookingStatus::Confirmed];
        if (! in_array($booking->status, $allowedStatuses)) {
            return response()->json(['error' => 'Нельзя переместить бронирование в текущем статусе.'], 422);
        }

        $checkIn  = $request->check_in_date;
        $checkOut = $request->check_out_date;

        $conflict = DB::transaction(function () use ($booking, $checkIn, $checkOut) {
            return ! $this->availability->isAvailableLocked(
                Room::lockForUpdate()->findOrFail($booking->room_id),
                $checkIn,
                $checkOut,
                $booking->id,
            );
        });

        if ($conflict) {
            return response()->json(['error' => 'Номер уже занят на эти даты.'], 409);
        }

        $nights     = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));
        $totalPrice = $nights * (float) optional(optional($booking->room)->roomType)->base_price;

        $booking->update([
            'check_in_date'  => $checkIn,
            'check_out_date' => $checkOut,
            'total_price'    => $totalPrice ?: $booking->total_price,
        ]);

        return response()->json(['ok' => true]);
    }

    public function extendStay(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'extra_nights' => ['required', 'integer', 'min:1', 'max:90'],
        ]);

        $allowedStatuses = [BookingStatus::Confirmed, BookingStatus::CheckedIn];
        if (! in_array($booking->status, $allowedStatuses)) {
            return response()->json(['error' => 'Нельзя продлить бронирование в текущем статусе.'], 422);
        }

        $currentCheckOut = $booking->check_out_date->toDateString();
        $newCheckOut     = $booking->check_out_date->addDays((int) $request->extra_nights)->toDateString();

        $conflict = DB::transaction(function () use ($booking, $currentCheckOut, $newCheckOut) {
            return ! $this->availability->isAvailableLocked(
                Room::lockForUpdate()->findOrFail($booking->room_id),
                $currentCheckOut,
                $newCheckOut,
                $booking->id,
            );
        });

        if ($conflict) {
            $conflictBooking = Booking::with('guest')
                ->where('room_id', $booking->room_id)
                ->where('id', '!=', $booking->id)
                ->whereNotIn('status', [
                    BookingStatus::Cancelled->value,
                    BookingStatus::CheckedOut->value,
                    BookingStatus::NoShow->value,
                ])
                ->where('check_in_date', '<', $newCheckOut)
                ->where('check_out_date', '>', $currentCheckOut)
                ->first();

            $conflictData = null;
            if ($conflictBooking) {
                $guest = $conflictBooking->guest;
                $conflictData = [
                    'id'         => $conflictBooking->id,
                    'guest_name' => $guest ? trim($guest->first_name . ' ' . $guest->last_name) : '—',
                    'check_in'   => $conflictBooking->check_in_date->format('d.m.Y'),
                    'check_out'  => $conflictBooking->check_out_date->format('d.m.Y'),
                    'url'        => route('bookings.show', $conflictBooking),
                ];
            }

            return response()->json([
                'error'    => 'Номер уже занят на эти дополнительные дни.',
                'conflict' => $conflictData,
            ], 409);
        }

        $room           = $booking->room()->with('roomType')->first();
        $totalNights    = $booking->check_in_date->diffInDays(Carbon::parse($newCheckOut));
        $pricePerNight  = (float) optional(optional($room)->roomType)->base_price;
        $totalPrice     = $totalNights * $pricePerNight;

        $booking->update([
            'check_out_date' => $newCheckOut,
            'total_price'    => $totalPrice ?: $booking->total_price,
        ]);

        return response()->json([
            'ok'            => true,
            'new_check_out' => $newCheckOut,
            'total_price'   => $totalPrice,
        ]);
    }

    public function invoice(Booking $booking): Response
    {
        // Generate invoice number if missing
        if (! $booking->invoice_number) {
            $count = Booking::whereNotNull('invoice_number')->count() + 1;
            $booking->invoice_number = 'INV-' . now()->year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            $booking->save();
        }

        $booking->load([
            'guest',
            'room.roomType',
            'payments' => fn($q) => $q->orderBy('paid_at'),
            'charges'  => fn($q) => $q->orderBy('created_at'),
            'creator',
        ]);

        $totals = [
            'room_cost'   => (float) $booking->total_price,
            'charges'     => $this->totals->chargesTotal($booking),
            'grand_total' => $this->totals->grandTotal($booking),
            'paid'        => $this->totals->paidAmount($booking),
            'deposit'     => $this->totals->depositAmount($booking),
            'balance_due' => $this->totals->balanceDue($booking),
        ];

        $hotel = config('hotel');

        // Verification URL text (no image needed - more reliable for PDF)
        $verifyUrl = route('bookings.show', $booking);

        $pdf = Pdf::loadView('bookings.pdf.invoice', compact('booking', 'totals', 'hotel', 'verifyUrl'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('invoice-' . $booking->invoice_number . '.pdf');
    }

    public function sendConfirmation(Booking $booking): RedirectResponse
    {
        $booking->load(['guest', 'room.roomType']);

        if (! $booking->guest?->email) {
            return back()->with('error', 'У гостя нет email-адреса.');
        }

        \Illuminate\Support\Facades\Mail::to($booking->guest->email)
            ->send(new GuestBookingConfirmed($booking));

        return back()->with('success', "Письмо отправлено на {$booking->guest->email}.");
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        abort_unless(
            in_array($booking->status->value, [BookingStatus::Cancelled->value, BookingStatus::Inquiry->value]),
            403,
            'Только отменённые бронирования можно удалить.'
        );

        $booking->delete();

        return redirect()->route('bookings.index')->with('success', 'Бронирование удалено.');
    }

    public function restore(int $id): RedirectResponse
    {
        abort_unless(auth()->user()->role->value === 'owner', 403);

        $booking = Booking::withTrashed()->findOrFail($id);
        $booking->restore();

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Бронирование восстановлено.');
    }

    public function export(Request $request): \Illuminate\Http\Response
    {
        $query = Booking::with(['guest', 'room.roomType'])->orderBy('check_in_date', 'desc');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->query('search')) {
            $query->whereHas('guest', fn($q) => $q
                ->whereRaw('first_name ILIKE ?', ["%{$search}%"])
                ->orWhereRaw('last_name ILIKE ?', ["%{$search}%"])
                ->orWhereRaw('phone ILIKE ?', ["%{$search}%"]));
        }
        if ($checkIn = $request->query('check_in')) {
            $query->where('check_in_date', '>=', $checkIn);
        }
        if ($checkOut = $request->query('check_out')) {
            $query->where('check_out_date', '<=', $checkOut);
        }
        if ($room = $request->query('room')) {
            $query->whereHas('room', fn($q) => $q->whereRaw('number ILIKE ?', ["%{$room}%"]));
        }

        $bookings = $query->get();

        $rows   = [];
        $rows[] = ['ID', 'Код', 'Гость', 'Телефон', 'Номер', 'Тип', 'Заезд', 'Выезд', 'Ночей', 'Взрослых', 'Детей', 'Статус', 'Источник', 'Сумма', 'Скидка', 'Промокод'];
        foreach ($bookings as $b) {
            $nights = $b->check_in_date->diffInDays($b->check_out_date);
            $rows[] = [
                $b->id,
                $b->booking_ref ?? '',
                $b->guest?->full_name ?? '',
                $b->guest?->phone ?? '',
                $b->room?->number ?? '',
                $b->room?->roomType?->name ?? '',
                $b->check_in_date->format('d.m.Y'),
                $b->check_out_date->format('d.m.Y'),
                $nights,
                $b->adults,
                $b->children,
                $b->status->label(),
                $b->source->label(),
                number_format((float) $b->total_price, 2, '.', ''),
                number_format((float) ($b->discount_amount ?? 0), 2, '.', ''),
                $b->applied_promo_code ?? '',
            ];
        }

        $csv = implode("\n", array_map(fn($row) => implode(';', array_map(
            fn($v) => '"' . str_replace('"', '""', (string) $v) . '"',
            $row
        )), $rows));

        return response("\xEF\xBB\xBF" . $csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="bookings_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
