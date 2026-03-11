<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    protected $model = \App\Models\Country::class;
    public function definition()
    {
        $countries = [
            ['name' => 'Россия', 'code' => 'RU'],
            ['name' => 'США', 'code' => 'US'],
            ['name' => 'Италия', 'code' => 'IT'],
            ['name' => 'Франция', 'code' => 'FR'],
            ['name' => 'Япония', 'code' => 'JP'],
            ['name' => 'Китай', 'code' => 'CN'],
            ['name' => 'Испания', 'code' => 'ES'],
            ['name' => 'Таиланд', 'code' => 'TH'],
            ['name' => 'Мексика', 'code' => 'MX'],
            ['name' => 'Греция', 'code' => 'GR'],
        ];
        
        $country = fake()->unique()->randomElement($countries);
        
        return [
            'name' => $country['name'],
            'code' => $country['code'],
        ];
    }
}