<?php

namespace App\Services;

use App\Models\Table;
use App\Models\Booking;

class TableService
{
    public function getTablesByRestaurant($restaurantId)
    {
        return Table::where('restaurant_id', $restaurantId)
            ->withCount(['bookings' => function($q) {
                $q->where('status', Booking::STATUS_ACTIVE);
            }])
            ->get();
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
            throw new \Exception('Нельзя удалить столик с активными бронированиями');
        }

        return $table->delete();
    }
}