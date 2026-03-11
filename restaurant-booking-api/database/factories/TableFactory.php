<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Restaurant;

class TableFactory extends Factory
{
    protected $model = \App\Models\Table::class;
    public function definition()
    {
        return [
            'restaurant_id' => Restaurant::inRandomOrder()->first()->id,
            'table_number' => 'T' . fake()->numberBetween(1, 100),
            'capacity' => fake()->randomElement([2, 4, 4, 4, 6, 6, 8, 10]),
            'is_available' => true,
        ];
    }
}