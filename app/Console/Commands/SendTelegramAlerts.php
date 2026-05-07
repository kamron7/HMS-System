<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendTelegramAlerts extends Command
{
    protected $signature   = 'telegram:alerts';
    protected $description = 'Send Telegram alerts for stale bookings, unconfirmed check-ins, and overdue payments';

    public function handle(TelegramService $tg): int
    {
        $today = now()->toDateString();

        // 1. Unconfirmed check-ins today (pending/confirmed but not yet checked in)
        Booking::whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
            ->whereDate('check_in_date', $today)
            ->with(['guest', 'room'])
            ->get()
            ->each(function (Booking $b) use ($tg, $today) {
                $key = "tg_checkin_{$b->id}_{$today}";
                if (Cache::add($key, 1, now()->endOfDay())) {
                    $tg->sendTyped('alert_checkin_unconfirmed', ['owner', 'manager', 'receptionist'],
                        "📅 <b>Заезд не подтверждён #{$b->id}</b>\n" .
                        "Гость: " . ($b->guest?->full_name ?? '—') . "\n" .
                        "Номер: " . ($b->room?->number ?? '—') . " — заезд сегодня, ещё не заселён"
                    );
                }
            });

        // 2. Pending stale >24h
        Booking::where('status', BookingStatus::Pending->value)
            ->where('created_at', '<', now()->subHours(24))
            ->with(['guest', 'room'])
            ->get()
            ->each(function (Booking $b) use ($tg, $today) {
                $key = "tg_stale_{$b->id}_{$today}";
                if (Cache::add($key, 1, now()->endOfDay())) {
                    $tg->sendTyped('alert_stale_pending', ['owner', 'manager'],
                        "⏰ <b>Бронирование ожидает >24ч #{$b->id}</b>\n" .
                        "Гость: " . ($b->guest?->full_name ?? '—') . "\n" .
                        "Создано: " . Carbon::parse($b->created_at)->format('d.m.Y H:i')
                    );
                }
            });

        // 3. Overdue payments (checked out, not fully paid)
        Booking::where('status', BookingStatus::CheckedOut->value)
            ->with('payments')
            ->get()
            ->filter(fn(Booking $b) => $b->paymentStatus() !== 'paid')
            ->each(function (Booking $b) use ($tg, $today) {
                $key = "tg_overdue_{$b->id}_{$today}";
                if (Cache::add($key, 1, now()->endOfDay())) {
                    $tg->sendTyped('alert_payment_overdue', ['owner', 'manager'],
                        "💸 <b>Задолженность по оплате #{$b->id}</b>\n" .
                        "Гость: " . ($b->guest?->full_name ?? '—') . "\n" .
                        "Выезд без полной оплаты"
                    );
                }
            });

        // 4. Late checkouts — checked_in with check_out_date in the past
        Booking::where('status', BookingStatus::CheckedIn->value)
            ->where('check_out_date', '<', $today)
            ->with(['guest', 'room'])
            ->get()
            ->each(function (Booking $b) use ($tg, $today) {
                $key = "tg_late_checkout_{$b->id}_{$today}";
                if (Cache::add($key, 1, now()->endOfDay())) {
                    $tg->sendTyped('alert_late_checkout', ['owner', 'manager', 'receptionist'],
                        "🕐 <b>Просроченный выезд #{$b->id}</b>\n" .
                        "Гость: " . ($b->guest?->full_name ?? '—') . "\n" .
                        "Номер: " . ($b->room?->number ?? '—') . "\n" .
                        "Дата выезда: " . Carbon::parse($b->check_out_date)->format('d.m.Y')
                    );
                }
            });

        $this->info('Telegram alerts sent.');
        return self::SUCCESS;
    }
}
