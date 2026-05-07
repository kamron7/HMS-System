<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingCharge;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NightAuditCommand extends Command
{
    protected $signature   = 'audit:night {--date= : Date to audit (default: today)}';
    protected $description = 'Run nightly audit: post room-night charges and flag no-shows';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : today();

        $this->info("Night audit for {$date->toDateString()}");

        $charged  = 0;
        $noShowed = 0;

        DB::transaction(function () use ($date, &$charged, &$noShowed) {

            // 1. Post room-night charges for checked-in bookings
            $checkedIn = Booking::with(['room.roomType'])
                ->where('status', BookingStatus::CheckedIn->value)
                ->whereDate('check_in_date', '<=', $date)
                ->whereDate('check_out_date', '>', $date)
                ->get();

            foreach ($checkedIn as $booking) {
                $nightPrice = (float) optional(optional($booking->room)->roomType)->base_price ?? 0;
                if ($nightPrice <= 0) continue;

                $alreadyPosted = BookingCharge::where('booking_id', $booking->id)
                    ->where('category', 'room_night')
                    ->whereDate('created_at', $date)
                    ->exists();

                if (! $alreadyPosted) {
                    BookingCharge::create([
                        'booking_id'  => $booking->id,
                        'description' => 'Проживание ' . $date->translatedFormat('d M Y'),
                        'amount'      => $nightPrice,
                        'category'    => 'room_night',
                    ]);
                    $charged++;
                }
            }

            // 2. Flag no-shows: pending/confirmed bookings whose check-in was yesterday
            $yesterday = $date->copy()->subDay();
            $noShows   = Booking::whereIn('status', [
                    BookingStatus::Pending->value,
                    BookingStatus::Confirmed->value,
                ])
                ->whereDate('check_in_date', $yesterday)
                ->get();

            foreach ($noShows as $booking) {
                $booking->update(['status' => BookingStatus::NoShow->value]);
                $noShowed++;
            }
        });

        $this->info("  Room-night charges posted: {$charged}");
        $this->info("  No-shows flagged:           {$noShowed}");

        return self::SUCCESS;
    }
}
