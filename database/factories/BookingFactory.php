<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'room_id'        => Room::factory(),
            'guest_id'       => Guest::factory(),
            'check_in_date'  => today(),
            'check_out_date' => today()->addDay(),
            'adults'         => 2,
            'children'       => 0,
            'status'         => BookingStatus::Pending->value,
            'total_price'    => 100000,
            'notes'          => null,
            'created_by'     => User::factory(),
        ];
    }
}
