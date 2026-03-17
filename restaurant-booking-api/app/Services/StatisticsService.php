<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\Table;

class StatisticsService
{
    public function getTableStatistics($restaurantId = null)
    {
        $query = Table::query();
        
        if ($restaurantId) {
            $query->where('restaurant_id', $restaurantId);
        }

        $tables = $query->with('restaurant')->get();
        
        return $tables->map(function($table) {
            $totalBookings = Booking::where('table_id', $table->id)->count();
            $completedBookings = Booking::where('table_id', $table->id)
                ->where('status', Booking::STATUS_COMPLETED)
                ->count();

            return [
                'table_id' => $table->id,
                'table_number' => $table->table_number,
                'restaurant' => $table->restaurant->name,
                'capacity' => $table->capacity,
                'statistics' => [
                    'total_bookings' => $totalBookings,
                    'completed_bookings' => $completedBookings,
                    'occupancy_rate' => $totalBookings > 0 
                        ? round(($completedBookings / $totalBookings) * 100, 2) 
                        : 0
                ]
            ];
        });
    }

    public function getBookingsStatistics()
    {
        $now = now();
        $today = $now->format('Y-m-d');
        
        return [
            'total_bookings' => Booking::count(),
            'active_bookings' => Booking::where('status', Booking::STATUS_ACTIVE)->count(),
            'cancelled_bookings' => Booking::where('status', Booking::STATUS_CANCELLED)->count(),
            'completed_bookings' => Booking::where('status', Booking::STATUS_COMPLETED)->count(),
            'today_bookings' => Booking::where('booking_date', $today)->count(),
        ];
    }
}