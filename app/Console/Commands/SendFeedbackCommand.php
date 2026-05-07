<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Mail\GuestFeedbackRequest;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendFeedbackCommand extends Command
{
    protected $signature   = 'feedback:send';
    protected $description = 'Send post-stay feedback requests to guests who checked out 1–2 days ago';

    public function handle(): int
    {
        $from = today()->subDays(2);
        $to   = today()->subDay();

        $bookings = Booking::with(['guest'])
            ->where('status', BookingStatus::CheckedOut->value)
            ->whereBetween('check_out_date', [$from, $to])
            ->where('feedback_sent', false)
            ->whereHas('guest', fn($q) => $q->whereNotNull('email'))
            ->get();

        $sent = 0;
        foreach ($bookings as $booking) {
            $url = URL::signedRoute('feedback.show', ['booking' => $booking->id]);

            Mail::to($booking->guest->email)
                ->send(new GuestFeedbackRequest($booking, $url));

            $booking->update(['feedback_sent' => true]);
            $sent++;
        }

        $this->info("Feedback requests sent: {$sent}");

        return self::SUCCESS;
    }
}
