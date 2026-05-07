<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Booking;

class BookingObserver
{
    public function created(Booking $booking): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'booking.created',
            subjectType: 'Booking',
            subjectId: $booking->id,
            subjectLabel: "Бронирование #{$booking->id}",
            newValues: [
                'status'   => $booking->status->value,
                'room_id'  => $booking->room_id,
                'guest_id' => $booking->guest_id,
            ]
        );
    }

    public function updated(Booking $booking): void
    {
        if (! auth()->check()) return;

        if ($booking->wasChanged('status')) {
            ActivityLog::record(
                action: 'booking.status_changed',
                subjectType: 'Booking',
                subjectId: $booking->id,
                subjectLabel: "Бронирование #{$booking->id}",
                oldValues: ['status' => $booking->getOriginal('status')],
                newValues: ['status' => $booking->status->value]
            );
        } else {
            $skip = ['updated_at', 'created_at', 'invoice_number', 'booking_ref'];
            $dirty = collect($booking->getDirty())->except($skip)->all();

            if (empty($dirty)) return;

            ActivityLog::record(
                action: 'booking.updated',
                subjectType: 'Booking',
                subjectId: $booking->id,
                subjectLabel: "Бронирование #{$booking->id}",
                newValues: $dirty
            );
        }
    }

    public function deleted(Booking $booking): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'booking.deleted',
            subjectType: 'Booking',
            subjectId: $booking->id,
            subjectLabel: "Бронирование #{$booking->id}"
        );
    }
}
