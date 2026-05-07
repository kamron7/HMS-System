<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\GuestReview;
use App\Services\PricingService;
use App\Services\RoomAvailabilityService;
use App\Services\RoomSuggestService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $query = Room::with(['roomType', 'bookings' => function ($q) {
            $q->where('status', BookingStatus::CheckedIn->value)
              ->with('guest')
              ->latest()
              ->limit(1);
        }]);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'ilike', "%{$search}%")
                  ->orWhereHas('bookings', function ($bq) use ($search) {
                      $bq->where('status', BookingStatus::CheckedIn->value)
                         ->whereHas('guest', function ($gq) use ($search) {
                             $gq->where('first_name', 'ilike', "%{$search}%")
                                ->orWhere('last_name', 'ilike', "%{$search}%");
                         });
                  });
            });
        }

        $rooms = $query->orderBy('floor')->orderBy('number')->get()->groupBy('floor');

        // Counts for filter pills
        $counts = Room::selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        // Overdue checked-in bookings (checkout date passed, guest still present)
        $overdueBookings = \App\Models\Booking::with(['room', 'guest'])
            ->where('status', BookingStatus::CheckedIn->value)
            ->whereDate('check_out_date', '<', today())
            ->get()
            ->keyBy('room_id');

        return view('rooms.index', compact('rooms', 'counts', 'overdueBookings'));
    }

    public function create(): View
    {
        $roomTypes = RoomType::orderBy('name')->get();
        $statuses  = RoomStatus::cases();

        return view('rooms.create', compact('roomTypes', 'statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'number'       => ['required', 'string', 'max:10', 'unique:rooms,number'],
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'floor'        => ['nullable', 'integer', 'min:1', 'max:100'],
            'status'       => ['required', 'string', 'in:' . implode(',', array_column(RoomStatus::cases(), 'value'))],
            'notes'        => ['nullable', 'string', 'max:1000'],
            'images'       => ['nullable', 'array', 'min:3', 'max:10'],
            'images.*'     => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $room = Room::create(\Illuminate\Support\Arr::except($validated, ['images']));

        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $file) {
                $paths[] = $file->store("rooms/{$room->id}", 'public');
            }
            $room->update(['images' => $paths]);
        }

        return redirect()->route('rooms.index')
            ->with('success', 'Номер успешно добавлен.');
    }

    public function edit(Room $room): View
    {
        $roomTypes = RoomType::orderBy('name')->get();
        $statuses  = RoomStatus::cases();
        $reviews   = $room->reviews()->with(['guest', 'booking'])->latest('submitted_at')->limit(20)->get();

        return view('rooms.edit', compact('room', 'roomTypes', 'statuses', 'reviews'));
    }

    public function qrCode(Room $room)
    {
        $url = route('room-portal.show', $room->qr_token);

        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg    = $writer->writeString($url);

        return response($svg, 200, [
            'Content-Type'        => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="room-' . $room->number . '-qr.svg"',
        ]);
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $existing = $room->images ?? [];
        $maxNew   = 10 - count($existing);

        $validated = $request->validate([
            'number'       => ['required', 'string', 'max:10', 'unique:rooms,number,' . $room->id],
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'floor'        => ['nullable', 'integer', 'min:1', 'max:100'],
            'status'       => ['required', 'string', 'in:' . implode(',', array_column(RoomStatus::cases(), 'value'))],
            'notes'        => ['nullable', 'string', 'max:1000'],
            'images'       => ['nullable', 'array', 'max:' . max($maxNew, 0)],
            'images.*'     => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $room->update(\Illuminate\Support\Arr::except($validated, ['images']));

        if ($request->hasFile('images')) {
            $paths = $existing;
            foreach ($request->file('images') as $file) {
                if (count($paths) >= 10) {
                    break;
                }
                $paths[] = $file->store("rooms/{$room->id}", 'public');
            }
            $room->update(['images' => $paths]);
        }

        return redirect()->route('rooms.edit', $room)
            ->with('success', 'Номер успешно обновлён.');
    }

    public function deleteImage(Request $request, Room $room): JsonResponse
    {
        $request->validate(['path' => ['required', 'string']]);
        $path = $request->path;

        $images = $room->images ?? [];
        if (! in_array($path, $images, true)) {
            return response()->json(['error' => 'Файл не найден.'], 404);
        }

        Storage::disk('public')->delete($path);
        $room->update(['images' => array_values(array_filter($images, fn($p) => $p !== $path))]);

        return response()->json(['ok' => true]);
    }

    public function updateStatus(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', array_column(RoomStatus::cases(), 'value'))],
        ]);

        $room->update(['status' => $validated['status']]);

        return redirect()->route('rooms.index')->with('success', 'Статус обновлён.');
    }

    public function checkAvailability(Request $request, Room $room, RoomAvailabilityService $availability): JsonResponse
    {
        $checkIn  = $request->query('check_in');
        $checkOut = $request->query('check_out');

        if (! $checkIn || ! $checkOut) {
            return response()->json(['available' => false, 'error' => 'Не указаны даты.']);
        }

        if (! $room->status->canBook()) {
            return response()->json([
                'available'   => false,
                'room_status' => $room->status->value,
                'room_status_label' => $room->status->label(),
                'conflicts'   => [],
            ]);
        }

        if ($availability->isAvailable($room, $checkIn, $checkOut)) {
            return response()->json(['available' => true]);
        }

        $today = now()->toDateString();

        $conflicts = $room->bookings()
            ->with('guest')
            ->where(function ($q) use ($checkIn, $checkOut, $today) {
                // Normal active bookings overlapping the range
                $q->where(function ($q2) use ($checkIn, $checkOut) {
                    $q2->whereIn('status', [
                            \App\Enums\BookingStatus::Pending->value,
                            \App\Enums\BookingStatus::Confirmed->value,
                            \App\Enums\BookingStatus::Inquiry->value,
                        ])
                        ->where('check_in_date', '<', $checkOut)
                        ->where('check_out_date', '>', $checkIn);
                })
                // Overdue checked-in: guest still present past their checkout date
                ->orWhere(function ($q2) use ($checkIn, $checkOut, $today) {
                    $q2->where('status', \App\Enums\BookingStatus::CheckedIn->value)
                        ->where('check_in_date', '<', $checkOut)
                        ->where(function ($q3) use ($checkIn, $today) {
                            $q3->where('check_out_date', '>', $checkIn)
                               ->orWhere('check_out_date', '<=', $today);
                        });
                });
            })
            ->get()
            ->map(fn($b) => [
                'guest'      => trim(optional($b->guest)->first_name . ' ' . optional($b->guest)->last_name),
                'phone'      => optional($b->guest)->phone ?? '',
                'check_in'   => $b->check_in_date->toDateString(),
                'check_out'  => $b->check_out_date->toDateString(),
                'status'     => $b->status->value,
                'status_label' => $b->status->label(),
                'url'        => route('bookings.show', $b),
                'overdue'    => $b->status->value === \App\Enums\BookingStatus::CheckedIn->value
                                && $b->check_out_date->toDateString() <= $today,
            ]);

        return response()->json(['available' => false, 'conflicts' => $conflicts]);
    }

    public function available(Request $request, PricingService $pricing)
    {
        $checkIn  = $request->query('check_in');
        $checkOut = $request->query('check_out');

        $rooms = Room::with('roomType')
            ->where('status', RoomStatus::Available->value)
            ->get()
            ->filter(fn(Room $room) => $room->isAvailable($checkIn, $checkOut))
            ->values()
            ->map(function (Room $room) use ($checkIn, $checkOut, $pricing) {
                $data = $room->toArray();
                $data['image_url'] = !empty($data['images'])
                    ? asset('storage/' . $data['images'][0])
                    : null;
                $data['price_per_night'] = ($checkIn && $checkOut)
                    ? $pricing->adjustedPrice($room->roomType, $checkIn, $checkOut)
                    : (float) $room->roomType->base_price;
                $data['pricing_banner'] = ($checkIn && $checkOut)
                    ? $pricing->activeBanner($room->roomType, $checkIn, $checkOut)
                    : null;
                return $data;
            });

        return response()->json($rooms);
    }

    public function suggest(Request $request, RoomSuggestService $suggester, RoomAvailabilityService $availability): JsonResponse
    {
        $request->validate([
            'guest_id'  => 'required|integer|exists:guests,id',
            'check_in'  => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'adults'    => 'integer|min:1',
        ]);

        $suggestions = $suggester->suggest(
            guestId:      (int) $request->guest_id,
            checkIn:      $request->check_in,
            checkOut:     $request->check_out,
            adults:       (int) ($request->adults ?? 1),
            availability: $availability,
        );

        return response()->json($suggestions);
    }

    public function deleteReview(GuestReview $review): RedirectResponse
    {
        if ($review->photos) {
            foreach (explode(',', $review->photos) as $photo) {
                Storage::disk('public')->delete(trim($photo));
            }
        }

        $review->delete();

        return back()->with('success', 'Отзыв удалён.');
    }

    public function reviewsIndex(Request $request): View
    {
        $query = GuestReview::with(['room', 'guest', 'booking'])
            ->orderByDesc('submitted_at');

        // Star filter
        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        // Room filter
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('submitted_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('submitted_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'ilike', "%{$search}%")
                  ->orWhereHas('guest', function ($gq) use ($search) {
                      $gq->where('first_name', 'ilike', "%{$search}%")
                         ->orWhere('last_name', 'ilike', "%{$search}%");
                  });
            });
        }

        $reviews = $query->paginate(20);

        // Stats
        $allReviews = GuestReview::all();
        $avgRating = $allReviews->avg('rating');
        $counts = GuestReview::selectRaw('rating, count(*) as cnt')
            ->groupBy('rating')
            ->pluck('cnt', 'rating');

        $rooms = Room::with('roomType')->orderBy('number')->get();

        return view('reviews.index', compact('reviews', 'avgRating', 'counts', 'rooms'));
    }
}
