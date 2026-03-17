<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\Booking;
use App\Models\Table;

class RestaurantService
{
    
    public function getAllRestaurants($filters = [])
    {
        $query = Restaurant::with(['city.country'])
            ->withAvg('reviews', 'rating');
            
        if (isset($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }
        
        return $query->paginate(15);
    }

    public function getRestaurantById($id)
    {
        return Restaurant::with(['city.country', 'tables'])
            ->withAvg('reviews', 'rating')
            ->findOrFail($id);
    }
    
    public function getSchedule($restaurantId, $date)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        
        $bookings = Booking::whereHas('table', fn($q) => $q->where('restaurant_id', $restaurantId))
            ->where('booking_date', $date)
            ->where('status', Booking::STATUS_ACTIVE)
            ->get();
        
        $tables = Table::where('restaurant_id', $restaurantId)->get();
        
        $schedule = [];
        
        foreach ($tables as $table) {
            $tableBookings = $bookings->where('table_id', $table->id);
            
            $schedule[] = [
                'table_number' => $table->table_number,
                'capacity' => $table->capacity,
                'booked_times' => $tableBookings->map(fn($b) => substr($b->booking_time, 0, 5))->values()
            ];
        }
        
        return [
            'restaurant' => $restaurant->name,
            'date' => $date,
            'tables' => $schedule
        ];
    }
}