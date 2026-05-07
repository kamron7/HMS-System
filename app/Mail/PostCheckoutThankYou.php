<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PostCheckoutThankYou extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Booking $booking, public readonly string $feedbackUrl = '') {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Спасибо за проживание! — ' . config('hotel.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.post_checkout',
        );
    }
}
