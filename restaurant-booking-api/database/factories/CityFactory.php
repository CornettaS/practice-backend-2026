<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Country;

class CityFactory extends Factory
{
    protected $model = \App\Models\City::class;
    public function definition()
    {
        $citiesByCountry = [
            'Россия' => ['Москва', 'Санкт-Петербург', 'Казань', 'Сочи', 'Новосибирск'],
            'США' => ['Нью-Йорк', 'Лос-Анджелес', 'Чикаго', 'Майами', 'Лас-Вегас'],
            'Италия' => ['Рим', 'Милан', 'Венеция', 'Флоренция', 'Неаполь'],
            'Франция' => ['Париж', 'Лион', 'Марсель', 'Ницца', 'Бордо'],
            'Япония' => ['Токио', 'Осака', 'Киото', 'Саппоро', 'Иокогама'],
            'Китай' => ['Пекин', 'Шанхай', 'Гуанчжоу', 'Гонконг', 'Сиань'],
            'Испания' => ['Мадрид', 'Барселона', 'Валенсия', 'Севилья', 'Гранада'],
            'Таиланд' => ['Бангкок', 'Пхукет', 'Патайя', 'Чиангмай', 'Краби'],
            'Мексика' => ['Мехико', 'Канкун', 'Гвадалахара', 'Монтеррей', 'Тихуана'],
            'Греция' => ['Афины', 'Салоники', 'Санторини', 'Крит', 'Родос'],
        ];

        $country = Country::inRandomOrder()->first();
        $cityName = fake()->randomElement($citiesByCountry[$country->name] ?? ['Москва', 'Париж', 'Токио']);

        return [
            'country_id' => $country->id,
            'name' => $cityName,
        ];
    }
}