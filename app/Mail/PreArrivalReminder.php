<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PreArrivalReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Booking $booking, public readonly string $portalUrl = '') {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Напоминаем о вашем заезде завтра — ' . config('hotel.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pre_arrival',
        );
    }
}
