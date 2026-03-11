<?php

namespace App\Services;

use App\Models\Restaurant;

class RestaurantService
{

    public function getAllRestaurants()
    {
        return Restaurant::with(['city.country'])->get();
    }


    public function getRestaurantById($id)
    {
        return Restaurant::with(['city.country', 'tables'])->findOrFail($id);
    }

    public function createRestaurant($data)
    {
        return Restaurant::create($data);
    }

    public function updateRestaurant($id, $data)
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->update($data);
        return $restaurant;
    }

    public function deleteRestaurant($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->delete();
        return true;
    }
}