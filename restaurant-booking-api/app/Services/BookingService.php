<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Table;
use Carbon\Carbon;

class BookingService
{

    public function checkAvailability($tableId, $date, $time, $guestsCount)
    {
        $table = Table::with('restaurant')->findOrFail($tableId);
        
        if ($table->capacity < $guestsCount) {
            return [
                'available' => false,
                'message' => 'Столик не вмещает ' . $guestsCount . ' гостей. Максимум: ' . $table->capacity
            ];
        }

        $restaurant = $table->restaurant;
        
        if ($time < substr($restaurant->opening_time, 0, 5) || 
            $time > substr($restaurant->closing_time, 0, 5)) {
            return [
                'available' => false,
                'message' => 'Ресторан работает с ' . 
                    substr($restaurant->opening_time, 0, 5) . ' до ' . 
                    substr($restaurant->closing_time, 0, 5)
            ];
        }

        $existingBooking = Booking::where('table_id', $tableId)
            ->where('booking_date', $date)
            ->where('booking_time', $time . ':00')
            ->where('status', Booking::STATUS_ACTIVE)
            ->exists();

        if ($existingBooking) {
            return [
                'available' => false,
                'message' => 'Этот столик уже забронирован на указанное время'
            ];
        }

        return ['available' => true];
    }

    public function createBooking($userId, $data)
    {
        return Booking::create([
            'user_id' => $userId,
            'table_id' => $data['table_id'],
            'booking_date' => $data['booking_date'],
            'booking_time' => $data['booking_time'] . ':00',
            'guests_count' => $data['guests_count'],
            'status' => Booking::STATUS_ACTIVE
        ]);
    }


    public function getUserBookings($userId, $type = 'all')
    {
        $query = Booking::with(['table.restaurant'])
            ->where('user_id', $userId);

        if ($type === 'upcoming') {
            $query->where('status', Booking::STATUS_ACTIVE)
                  ->where('booking_date', '>=', now()->format('Y-m-d'));
        } elseif ($type === 'history') {
            $query->whereIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED]);
        }

        return $query->orderBy('booking_date', 'desc')
                     ->orderBy('booking_time', 'desc')
                     ->get();
    }


    public function cancelBooking($bookingId, $userId, $isAdmin = false)
    {
        $booking = Booking::findOrFail($bookingId);

        if (!$isAdmin && $booking->user_id !== $userId) {
            return [
                'success' => false,
                'message' => 'Доступ запрещен'
            ];
        }

        if ($booking->status !== Booking::STATUS_ACTIVE) {
            return [
                'success' => false,
                'message' => 'Бронирование уже ' . 
                    ($booking->status === Booking::STATUS_CANCELLED ? 'отменено' : 'завершено')
            ];
        }

        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        return [
            'success' => true,
            'message' => 'Бронирование успешно отменено'
        ];
    }
}