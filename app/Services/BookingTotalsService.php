<?php

namespace App\Services;

use App\Enums\PaymentType;
use App\Models\Booking;

class BookingTotalsService
{
    /**
     * Sum of all extra charges (excludes room base cost).
     */
    public function chargesTotal(Booking $booking): float
    {
        if ($booking->relationLoaded('charges')) {
            return (float) $booking->charges->sum('amount');
        }
        return (float) $booking->charges()->sum('amount');
    }

    /**
     * Grand total = room cost + all charges.
     */
    public function grandTotal(Booking $booking): float
    {
        return (float) $booking->total_price + $this->chargesTotal($booking);
    }

    /**
     * Sum of prepayments only (deposits excluded from balance).
     */
    public function paidAmount(Booking $booking): float
    {
        if ($booking->relationLoaded('payments')) {
            return (float) $booking->payments
                ->where('type', PaymentType::Prepayment->value)
                ->sum('amount');
        }
        return (float) $booking->payments()
            ->where('type', PaymentType::Prepayment->value)
            ->sum('amount');
    }

    /**
     * Sum of deposits (refundable, shown separately).
     */
    public function depositAmount(Booking $booking): float
    {
        if ($booking->relationLoaded('payments')) {
            return (float) $booking->payments
                ->where('type', PaymentType::Deposit->value)
                ->sum('amount');
        }
        return (float) $booking->payments()
            ->where('type', PaymentType::Deposit->value)
            ->sum('amount');
    }

    /**
     * Balance due = grand total − prepayments.
     */
    public function balanceDue(Booking $booking): float
    {
        return max(0, $this->grandTotal($booking) - $this->paidAmount($booking));
    }

    /**
     * Payment status label considering the grand total (not just total_price).
     */
    public function paymentStatus(Booking $booking): string
    {
        $paid  = $this->paidAmount($booking);
        $total = $this->grandTotal($booking);
        if ($paid <= 0) return 'unpaid';
        if ($paid < $total) return 'partial';
        return 'paid';
    }
}
