<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'amount'     => 50000,
            'method'     => 'cash',
            'paid_at'    => now(),
            'notes'      => null,
        ];
    }
}
