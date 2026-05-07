<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Models\Booking;
use App\Models\GuestReview;
use App\Models\GuestServiceRequest;
use App\Models\InAppNotification;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\User;
use App\Services\NotificationService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class RoomPortalController extends Controller
{
    private static function validStatuses(): array
    {
        return [
            BookingStatus::Pending->value,
            BookingStatus::Confirmed->value,
            BookingStatus::CheckedIn->value,
        ];
    }

    /** Returns the QR code SVG image for a room (public, by token). */
    public function qrImage(string $token)
    {
        $room     = Room::where('qr_token', $token)->firstOrFail();
        $url      = route('room-portal.show', $token);
        $renderer = new ImageRenderer(new RendererStyle(300), new SvgImageBackEnd());
        $svg      = (new Writer($renderer))->writeString($url);

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }

    /** Returns the QR code PNG image for PDF embedding (public, by token). */
    public function qrPng(string $token)
    {
        $room = Room::where('qr_token', $token)->firstOrFail();
        $url  = route('room-portal.show', $token);

        $tempFile = sys_get_temp_dir() . '/qr_' . $token . '.png';
        (new Writer(new ImageRenderer(new RendererStyle(200), new \BaconQrCode\Renderer\Image\ImagickImageBackEnd())))
            ->writeFile($url, $tempFile);

        $response = response(file_get_contents($tempFile), 200, [
            'Content-Type' => 'image/png',
        ]);

        @unlink($tempFile);
        return $response;
    }

    // ── Verification ─────────────────────────────────────────────────────────

    /** Show the booking verification form. */
    public function verifyForm(string $token)
    {
        $room = Room::where('qr_token', $token)->with('roomType')->firstOrFail();

        if ($this->resolveVerifiedBooking($room)) {
            return redirect()->route('room-portal.show', $token);
        }

        return view('room-portal.verify', compact('room', 'token'));
    }

    /** Verify the guest's booking number and grant session access. */
    public function verify(Request $request, string $token)
    {
        $room = Room::where('qr_token', $token)->firstOrFail();

        $request->validate([
            'booking_ref' => ['required', 'string', 'max:10'],
        ]);

        $ref = strtoupper(trim($request->booking_ref));

        // Prepend H- if the guest only typed the 6-char part
        if (! str_starts_with($ref, 'H-') && strlen($ref) === 6) {
            $ref = 'H-' . $ref;
        }

        $booking = $room->bookings()
            ->whereIn('status', self::validStatuses())
            ->where('booking_ref', $ref)
            ->with('guest')
            ->first();

        if (! $booking) {
            return back()->withErrors([
                'booking_ref' => 'Код бронирования не найден. Проверьте код или обратитесь на ресепшн.',
            ]);
        }

        session()->put($this->sessionKey($room), $booking->id);

        return redirect()->route('room-portal.show', $token);
    }

    // ── Portal ────────────────────────────────────────────────────────────────

    /** Main room portal page. */
    public function show(string $token)
    {
        $room = Room::where('qr_token', $token)->with('roomType')->firstOrFail();

        $booking = $this->resolveVerifiedBooking($room);
        if (! $booking) {
            return redirect()->route('room-portal.verify', $token);
        }

        $upsells              = collect(config('hotel.upsells'));
        $alreadyReviewed      = GuestReview::where('booking_id', $booking->id)->exists();
        $myRequests           = $booking->serviceRequests()->orderByDesc('created_at')->limit(20)->get();
        $myMaintenanceRequests = MaintenanceRequest::where('booking_id', $booking->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('room-portal.show', compact('room', 'booking', 'upsells', 'alreadyReviewed', 'token', 'myRequests', 'myMaintenanceRequests'));
    }

    /** Guest orders a service — charged to their booking. */
    public function order(Request $request, string $token)
    {
        $room    = Room::where('qr_token', $token)->firstOrFail();
        $booking = $this->resolveVerifiedBooking($room);

        if (! $booking) {
            return redirect()->route('room-portal.verify', $token);
        }

        $validated = $request->validate([
            'key' => 'required|string',
            'qty' => 'integer|min:1|max:10',
        ]);

        $qty     = (int) ($validated['qty'] ?? 1);
        $upsells = collect(config('hotel.upsells'))->keyBy('key');
        $upsell  = $upsells->get($validated['key']);

        if (! $upsell) {
            return back()->with('portal_error', 'Услуга не найдена.');
        }

        GuestServiceRequest::create([
            'booking_id'     => $booking->id,
            'room_id'        => $room->id,
            'upsell_key'     => $validated['key'],
            'label'          => $upsell['label'],
            'price_per_unit' => $upsell['price'],
            'quantity'       => $qty,
            'status'         => 'pending',
        ]);

        // Notify all active staff
        User::where('is_active', true)->each(function (User $user) use ($room, $booking, $upsell, $validated) {
            InAppNotification::create([
                'user_id'   => $user->id,
                'type'      => 'room_order',
                'title'     => "Заказ из номера {$room->number}",
                'body'      => "«{$upsell['label']}» — {$booking->guest->fullName}",
                'url'       => route('bookings.show', $booking),
                'reference' => "room_order_{$booking->id}_{$validated['key']}_" . now()->timestamp,
            ]);
        });

        return back()->with('ordered', $upsell['label']);
    }

    /** Guest submits feedback. */
    public function feedback(Request $request, string $token)
    {
        $room    = Room::where('qr_token', $token)->firstOrFail();
        $booking = $this->resolveVerifiedBooking($room);

        if (! $booking) {
            return redirect()->route('room-portal.verify', $token);
        }

        if (GuestReview::where('booking_id', $booking->id)->exists()) {
            return redirect()->route('room-portal.show', $token)
                ->with('portal_error', 'Вы уже оставили отзыв по этому бронированию.');
        }

        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'photos'  => ['nullable', 'array', 'max:3'],
            'photos.*' => ['image', 'max:5120'],
        ]);

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $photos[] = $file->store('feedback', 'public');
            }
        }

        $review = GuestReview::create([
            'room_id'      => $room->id,
            'booking_id'   => $booking->id,
            'guest_id'     => $booking->guest_id,
            'rating'       => $validated['rating'],
            'comment'      => $validated['comment'] ?? null,
            'submitted_at' => now(),
            'photos'       => $photos ? implode(',', $photos) : null,
        ]);

        if ($review->rating <= 2) {
            User::whereIn('role', ['owner', 'manager'])->each(function (User $user) use ($review, $room, $booking) {
                InAppNotification::create([
                    'user_id'   => $user->id,
                    'type'      => 'low_review',
                    'title'     => "Низкая оценка ({$review->rating}★) — номер {$room->number}",
                    'body'      => $review->comment ?? 'Без комментария',
                    'url'       => route('rooms.edit', $room),
                    'reference' => "low_review_{$review->id}",
                ]);
            });
        }

        // Notify all active staff about any new feedback
        User::where('is_active', true)->each(function (User $user) use ($review, $room, $booking) {
            InAppNotification::create([
                'user_id'   => $user->id,
                'type'      => 'guest_feedback',
                'title'     => "{$booking->guest->fullName} оставил отзыв — номер {$room->number}",
                'body'      => "{$review->rating}★" . ($review->comment ? ': ' . Str::limit($review->comment, 80) : ''),
                'url'       => route('rooms.edit', $room),
                'reference' => "feedback_{$review->id}",
            ]);
        });

        return redirect()->route('room-portal.show', $token)->with('feedback_sent', true);
    }

    /** Guest submits a maintenance / service request from their room. */
    public function maintenance(Request $request, string $token)
    {
        $room    = Room::where('qr_token', $token)->firstOrFail();
        $booking = $this->resolveVerifiedBooking($room);

        if (! $booking) {
            return redirect()->route('room-portal.verify', $token);
        }

        $validated = $request->validate([
            'category'    => 'required|string|in:кондиционер,сантехника,электрика,уборка,принадлежности,шум,другое',
            'description' => 'required|string|max:1000',
            'priority'    => 'nullable|string|in:low,medium,high,urgent',
            'photos'      => ['nullable', 'array', 'max:3'],
            'photos.*'    => ['image', 'max:5120'], // 5MB each
        ]);

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $photos[] = $file->store('maintenance', 'public');
            }
        }

        $priority = MaintenancePriority::tryFrom($validated['priority'] ?? 'medium')
            ?? MaintenancePriority::Medium;

        $req = MaintenanceRequest::create([
            'room_id'     => $room->id,
            'guest_id'    => $booking->guest_id,
            'booking_id'  => $booking->id,
            'category'    => $validated['category'],
            'title'       => ucfirst($validated['category']),
            'description' => $validated['description'],
            'priority'    => $priority,
            'status'      => MaintenanceStatus::Open,
            'created_by'  => null,
            'photos'      => $photos ? implode(',', $photos) : null,
        ]);

        // Notify staff via NotificationService
        app(NotificationService::class)->notifyNewMaintenance($req);

        return back()->with('maintenance_sent', $req->title);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function sessionKey(Room $room): string
    {
        return "room_portal_{$room->id}";
    }

    /** Returns the verified booking from session, or null if not verified / expired. */
    private function resolveVerifiedBooking(Room $room): ?Booking
    {
        $bookingId = session($this->sessionKey($room));

        if (! $bookingId) {
            return null;
        }

        $booking = $room->bookings()
            ->whereIn('status', self::validStatuses())
            ->with('guest', 'charges')
            ->find($bookingId);

        // Booking cancelled / checked-out since last visit — clear session
        if (! $booking) {
            session()->forget($this->sessionKey($room));
        }

        return $booking;
    }
}
