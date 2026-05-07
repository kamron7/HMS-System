<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\InAppNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $this->generateNotifications();

        $notifications = InAppNotification::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    public function count(Request $request)
    {
        $userId = auth()->id();
        $cacheKey = "notif_count_{$userId}";

        $count = Cache::remember($cacheKey, 25, function () use ($userId) {
            return InAppNotification::where('user_id', $userId)
                ->whereNull('read_at')
                ->count();
        });

        return response()->json(['count' => $count]);
    }

    public function markAllRead(Request $request)
    {
        InAppNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        Cache::forget("notif_count_{auth()->id()}");

        return redirect()->back()->with('success', 'Все уведомления прочитаны');
    }

    private function generateNotifications(): void
    {
        $user = auth()->user();
        $role = $user->role->value;
        $today = now()->toDateString();

        // Check-in unconfirmed: pending/confirmed with check_in_date = today
        if (in_array($role, ['owner', 'manager', 'receptionist'])) {
            $checkinBookings = Booking::whereIn('status', [
                    BookingStatus::Pending->value,
                    BookingStatus::Confirmed->value,
                ])
                ->whereDate('check_in_date', $today)
                ->get();

            foreach ($checkinBookings as $booking) {
                $ref = "checkin_{$booking->id}_{$today}";
                InAppNotification::createIfNotExists(
                    userId: $user->id,
                    type: 'checkin_unconfirmed',
                    title: 'Заезд не подтверждён',
                    body: "Бронирование #{$booking->id} — заезд сегодня, но ещё не заселён",
                    reference: $ref,
                    url: route('bookings.show', $booking)
                );
            }
        }

        // Payment overdue: checked_out with unpaid balance
        if (in_array($role, ['owner', 'manager'])) {
            $overdueBookings = Booking::where('status', BookingStatus::CheckedOut->value)
                ->with('payments')
                ->get()
                ->filter(fn($b) => $b->paymentStatus() !== 'paid');

            foreach ($overdueBookings as $booking) {
                $ref = "overdue_{$booking->id}";
                InAppNotification::createIfNotExists(
                    userId: $user->id,
                    type: 'payment_overdue',
                    title: 'Задолженность по оплате',
                    body: "Бронирование #{$booking->id} — выезд без полной оплаты",
                    reference: $ref,
                    url: route('bookings.show', $booking)
                );
            }

            // Pending stale: pending for >24h
            $staleBookings = Booking::where('status', BookingStatus::Pending->value)
                ->where('created_at', '<', now()->subHours(24))
                ->get();

            foreach ($staleBookings as $booking) {
                $ref = "stale_{$booking->id}_{$today}";
                InAppNotification::createIfNotExists(
                    userId: $user->id,
                    type: 'pending_stale',
                    title: 'Бронирование ожидает >24ч',
                    body: "Бронирование #{$booking->id} — более суток в статусе «Ожидает»",
                    reference: $ref,
                    url: route('bookings.show', $booking)
                );
            }
        }

        // Inquiry new: for all roles
        if (in_array($role, ['owner', 'manager', 'receptionist'])) {
            $inquiryBookings = Booking::where('status', BookingStatus::Inquiry->value)
                ->whereDate('created_at', $today)
                ->get();

            foreach ($inquiryBookings as $booking) {
                $ref = "inquiry_{$booking->id}";
                InAppNotification::createIfNotExists(
                    userId: $user->id,
                    type: 'inquiry_new',
                    title: 'Новый запрос от клиента',
                    body: "Бронирование #{$booking->id} — новый онлайн-запрос",
                    reference: $ref,
                    url: route('bookings.show', $booking)
                );
            }
        }
    }
}
