<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestFeedbackRequest extends Mailable
{
    use Queueable, SerializesModels;

    public string $feedbackUrl;

    public function __construct(public readonly Booking $booking, string $feedbackUrl)
    {
        $this->feedbackUrl = $feedbackUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Как вам понравилось? Оставьте отзыв — ' . config('hotel.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.feedback_request',
        );
    }
}
