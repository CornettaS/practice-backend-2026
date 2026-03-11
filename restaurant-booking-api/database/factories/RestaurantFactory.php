<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\City;

class RestaurantFactory extends Factory
{
    protected $model = \App\Models\Restaurant::class;
    public function definition()
    {
        $restaurantNames = [
            'Итальянский', 'Французский', 'Японский', 'Китайский', 'Мексиканский',
            'Грузинский', 'Тайский', 'Индийский', 'Американский', 'Греческий'
        ];

        $restaurantTypes = ['Ресторан', 'Бистро', 'Кафе', 'Лаунж', 'Паб', 'Суши-бар', 'Пиццерия'];
        
        return [
            'name' => fake()->randomElement($restaurantNames) . ' ' . fake()->randomElement($restaurantTypes),
            'city_id' => City::inRandomOrder()->first()->id,
            'address' => fake()->streetAddress(),
            'phone' => fake()->phoneNumber(),
            'description' => fake()->paragraphs(3, true),
            'capacity' => fake()->numberBetween(30, 200),
            'opening_time' => fake()->randomElement(['09:00', '10:00', '11:00', '12:00']),
            'closing_time' => fake()->randomElement(['22:00', '23:00', '00:00', '01:00']),
            'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 months', 'now')
        ];
    }
}