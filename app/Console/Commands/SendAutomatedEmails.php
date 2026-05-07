<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Mail\PostCheckoutThankYou;
use App\Mail\PreArrivalReminder;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendAutomatedEmails extends Command
{
    protected $signature = 'emails:send-automated';
    protected $description = 'Send pre-arrival reminders and post-checkout thank you emails';

    public function handle(): int
    {
        $this->sendPreArrivalReminders();
        $this->sendPostCheckoutThankYous();

        return Command::SUCCESS;
    }

    /** Send reminders to guests checking in tomorrow */
    private function sendPreArrivalReminders(): void
    {
        $tomorrow = today()->addDay();

        $bookings = Booking::whereDate('check_in_date', $tomorrow)
            ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Pending->value])
            ->whereHas('guest', fn($q) => $q->whereNotNull('email')->where('email', '!=', ''))
            ->with(['guest', 'room.roomType'])
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            if (! $booking->guest->email) continue;

            // Generate portal URL
            $portalUrl = $booking->room->qr_token
                ? route('room-portal.show', $booking->room->qr_token)
                : '';

            Mail::to($booking->guest->email)
                ->send(new PreArrivalReminder($booking, $portalUrl));

            $this->info("Pre-arrival reminder sent to {$booking->guest->email} (booking #{$booking->id})");
            $count++;
        }

        if ($count > 0) {
            $this->info("Sent {$count} pre-arrival reminder(s).");
        } else {
            $this->info('No pre-arrival reminders to send.');
        }
    }

    /** Send thank you emails to guests who just checked out */
    private function sendPostCheckoutThankYous(): void
    {
        // Guests who checked out in the last 2 hours
        $twoHoursAgo = now()->subHours(2);

        $bookings = Booking::where('status', BookingStatus::CheckedOut->value)
            ->where('updated_at', '>=', $twoHoursAgo)
            ->whereNull('feedback_sent')
            ->whereHas('guest', fn($q) => $q->whereNotNull('email')->where('email', '!=', ''))
            ->with(['guest', 'room'])
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            if (! $booking->guest->email) continue;

            // Generate feedback URL (signed, expires in 30 days)
            $feedbackUrl = URL::signedRoute('guest.booking.show', ['booking' => $booking->id], now()->addDays(30));

            Mail::to($booking->guest->email)
                ->send(new PostCheckoutThankYou($booking, $feedbackUrl));

            $booking->update(['feedback_sent' => true]);

            $this->info("Post-checkout thank you sent to {$booking->guest->email} (booking #{$booking->id})");
            $count++;
        }

        if ($count > 0) {
            $this->info("Sent {$count} post-checkout thank you email(s).");
        } else {
            $this->info('No post-checkout emails to send.');
        }
    }
}
