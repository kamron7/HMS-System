<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Payment;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'payment.created',
            subjectType: 'Payment',
            subjectId: $payment->id,
            subjectLabel: "Оплата #{$payment->id} к бронированию #{$payment->booking_id}",
            newValues: [
                'amount'     => $payment->amount,
                'type'       => $payment->type,
                'booking_id' => $payment->booking_id,
            ]
        );
    }

    public function updated(Payment $payment): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'payment.updated',
            subjectType: 'Payment',
            subjectId: $payment->id,
            subjectLabel: "Оплата #{$payment->id}",
            oldValues: $payment->getOriginal(),
            newValues: $payment->getDirty()
        );
    }

    public function deleted(Payment $payment): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'payment.deleted',
            subjectType: 'Payment',
            subjectId: $payment->id,
            subjectLabel: "Оплата #{$payment->id}"
        );
    }
}
