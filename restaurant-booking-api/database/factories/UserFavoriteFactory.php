<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Restaurant;
class UserFavoriteFactory extends Factory
{
    protected $model = \App\Models\UserFavorite::class;
    public function definition()
    {
        return [
            'user_id' => User::where('role', 'user')->inRandomOrder()->first()->id 
                ?? User::factory()->create(['role' => 'user'])->id,
            'restaurant_id' => Restaurant::inRandomOrder()->first()->id 
                ?? Restaurant::factory(),
            'created_at' => fake()->dateTimeBetween('-1 months', 'now'),
        ];
    }
}