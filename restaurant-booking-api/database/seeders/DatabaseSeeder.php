<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Country;
use App\Models\City;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Booking;
use App\Models\Review;
use App\Models\UserFavorite;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $countries = Country::factory(10)->create();

        foreach ($countries as $country) {
            City::factory(fake()->numberBetween(2, 5))->create([
                'country_id' => $country->id
            ]);
        }

        User::factory(50)->create(['role' => 'user']);
        User::factory(5)->create(['role' => 'admin']);

        Restaurant::factory(30)->create();

        $restaurants = Restaurant::all();
        foreach ($restaurants as $restaurant) {
            Table::factory(fake()->numberBetween(5, 15))->create([
                'restaurant_id' => $restaurant->id
            ]);
        }

        Booking::factory(100)->create();

        $completedBookings = Booking::where('status', 'completed')->take(40)->get();
        foreach ($completedBookings as $booking) {
            Review::factory()->create([
                'user_id' => $booking->user_id,
                'restaurant_id' => $booking->table->restaurant_id,
                'booking_id' => $booking->id
            ]);
        }

        
        $users = User::where('role', 'user')->get();
        $restaurantIds = Restaurant::pluck('id')->toArray();

        foreach ($users as $user) {
            $favoriteCount = fake()->numberBetween(0, 8);
            
            if ($favoriteCount > 0) {
                $randomRestaurantIds = fake()->randomElements($restaurantIds, $favoriteCount);
                
                foreach ($randomRestaurantIds as $restaurantId) {
                    UserFavorite::firstOrCreate([
                        'user_id' => $user->id,
                        'restaurant_id' => $restaurantId
                    ]);
                }
            }
        }

    }
}