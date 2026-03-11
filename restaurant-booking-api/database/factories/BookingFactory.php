<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Table;

class BookingFactory extends Factory
{
    protected $model = \App\Models\Booking::class;
    public function definition()
    {
        $date = fake()->dateTimeBetween('-1 month', '+2 months');
        $status = 'active';
        
        if ($date < now()) {
            $status = fake()->randomElement(['completed', 'cancelled']);
        }

        return [
            'user_id' => User::where('role', 'user')->inRandomOrder()->first()->id,
            'table_id' => Table::inRandomOrder()->first()->id,
            'booking_date' => $date->format('Y-m-d'),
            'booking_time' => fake()->randomElement(['18:00', '19:00', '20:00', '21:00']),
            'guests_count' => fake()->numberBetween(1, 8),
            'status' => $status,
            'created_at' => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }
}