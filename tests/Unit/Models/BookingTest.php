<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_status_is_unpaid_when_no_payments(): void
    {
        $booking = Booking::factory()->create(['total_price' => 100000]);

        $this->assertSame('unpaid', $booking->paymentStatus());
    }

    public function test_payment_status_is_partial_when_partial_payment(): void
    {
        $booking = Booking::factory()->create(['total_price' => 100000]);
        Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount'     => 50000,
        ]);

        $this->assertSame('partial', $booking->paymentStatus());
    }

    public function test_payment_status_is_paid_when_fully_paid(): void
    {
        $booking = Booking::factory()->create(['total_price' => 100000]);
        Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount'     => 100000,
        ]);

        $this->assertSame('paid', $booking->paymentStatus());
    }
}
