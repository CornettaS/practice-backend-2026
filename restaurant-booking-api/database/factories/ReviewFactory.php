<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Booking;

class ReviewFactory extends Factory
{
    protected $model = \App\Models\Review::class;
    public function definition()
    {
        $booking = Booking::where('status', 'completed')->inRandomOrder()->first();
        
        return [
            'user_id' => $booking->user_id,
            'restaurant_id' => $booking->table->restaurant_id,
            'booking_id' => $booking->id,
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->paragraph(),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}