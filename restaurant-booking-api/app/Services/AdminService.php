<?php
// app/Services/AdminService.php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Booking;
use App\Models\User;

class AdminService
{
    public function createRestaurant($data)
    {
        if (isset($data['opening_time'])) {
            $data['opening_time'] = $data['opening_time'] . ':00';
        }
        if (isset($data['closing_time'])) {
            $data['closing_time'] = $data['closing_time'] . ':00';
        }
        
        return Restaurant::create($data);
    }

    public function updateRestaurant($id, $data)
    {
        $restaurant = Restaurant::findOrFail($id);
        
        if (isset($data['opening_time'])) {
            $data['opening_time'] = $data['opening_time'] . ':00';
        }
        if (isset($data['closing_time'])) {
            $data['closing_time'] = $data['closing_time'] . ':00';
        }
        
        $restaurant->update($data);
        return $restaurant;
    }

    public function deleteRestaurant($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        
        $hasActiveBookings = Booking::whereHas('table', fn($q) => $q->where('restaurant_id', $id))
            ->where('status', Booking::STATUS_ACTIVE)
            ->exists();

        if ($hasActiveBookings) {
            throw new \Exception('Нельзя удалить ресторан с активными бронями');
        }
        
        return $restaurant->delete();
    }

    
    public function createTable($restaurantId, $data)
    {
        $existingTable = Table::where('restaurant_id', $restaurantId)
            ->where('table_number', $data['table_number'])
            ->exists();

        if ($existingTable) {
            throw new \Exception('Столик с таким номером уже существует');
        }

        return Table::create([
            'restaurant_id' => $restaurantId,
            'table_number' => $data['table_number'],
            'capacity' => $data['capacity'],
            'is_available' => true
        ]);
    }

    public function updateTable($tableId, $data)
    {
        $table = Table::findOrFail($tableId);

        if (isset($data['table_number']) && $data['table_number'] !== $table->table_number) {
            $existingTable = Table::where('restaurant_id', $table->restaurant_id)
                ->where('table_number', $data['table_number'])
                ->where('id', '!=', $tableId)
                ->exists();

            if ($existingTable) {
                throw new \Exception('Столик с таким номером уже существует');
            }
        }

        $table->update($data);
        return $table;
    }

    public function deleteTable($tableId)
    {
        $table = Table::findOrFail($tableId);
        
        $hasActiveBookings = Booking::where('table_id', $tableId)
            ->where('status', Booking::STATUS_ACTIVE)
            ->exists();

        if ($hasActiveBookings) {
            throw new \Exception('Нельзя удалить столик с активными бронями');
        }

        return $table->delete();
    }
    
    public function getAllBookings($filters = [])
    {
        $query = Booking::with(['user', 'table.restaurant']);
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['restaurant_id'])) {
            $query->whereHas('table', fn($q) => $q->where('restaurant_id', $filters['restaurant_id']));
        }
        
        if (isset($filters['date_from'])) {
            $query->where('booking_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('booking_date', '<=', $filters['date_to']);
        }
        
        return $query->orderBy('booking_date', 'desc')
                     ->paginate($filters['per_page'] ?? 20);
    }

    public function cancelBooking($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $booking->update(['status' => Booking::STATUS_CANCELLED]);
        return $booking;
    }

    public function getAllUsers($filters = [])
    {
        $query = User::query();
        
        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(20);
    }
}