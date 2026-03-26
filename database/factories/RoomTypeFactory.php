<?php

namespace Database\Factories;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomType>
 */
class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition(): array
    {
        return [
            'name'        => fake()->randomElement(['Стандарт', 'Делюкс', 'Люкс']),
            'base_price'  => fake()->numberBetween(50000, 200000),
            'capacity'    => fake()->numberBetween(2, 4),
            'description' => fake()->optional()->sentence(),
            'amenities'   => null,
        ];
    }
}
