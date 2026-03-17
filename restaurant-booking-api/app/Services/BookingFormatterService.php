<?php
namespace App\Services;

use App\Models\Booking;

class BookingFormatterService
{
    public function formatList($bookings, $withRussianStatus = false)
    {
        return $bookings->map(function($booking) use ($withRussianStatus) {
            $data = [
                'id' => $booking->id,
                'restaurant' => $booking->table->restaurant->name,
                'restaurant_id' => $booking->table->restaurant->id,
                'address' => $booking->table->restaurant->address,
                'date' => $booking->booking_date->format('Y-m-d'),
                'time' => $booking->booking_time_formatted,
                'end_time' => $booking->end_time_formatted,
                'duration' => $booking->duration,
                'guests' => $booking->guests_count,
                'table_number' => $booking->table->table_number,
                'table_id' => $booking->table->id,
                'status' => $booking->status
            ];
            
            if ($withRussianStatus) {
                $data['status_text'] = $this->getRussianStatus($booking->status);
            }
            
            return $data;
        });
    }
    
    public function formatDetail($booking, $withUser = false)
    {
        $data = [
            'id' => $booking->id,
            'restaurant' => [
                'id' => $booking->table->restaurant->id,
                'name' => $booking->table->restaurant->name,
                'address' => $booking->table->restaurant->address,
                'phone' => $booking->table->restaurant->phone,
            ],
            'table' => [
                'id' => $booking->table->id,
                'number' => $booking->table->table_number,
                'capacity' => $booking->table->capacity
            ],
            'date' => $booking->booking_date->format('Y-m-d'),
            'time' => $booking->booking_time_formatted,
            'end_time' => $booking->end_time_formatted,
            'duration' => $booking->duration,
            'guests' => $booking->guests_count,
            'status' => $booking->status,
            'status_text' => $this->getRussianStatus($booking->status),
            'created_at' => $booking->created_at ? $booking->created_at->format('Y-m-d H:i:s') : null,
        ];
        
        if ($withUser) {
            $data['user'] = [
                'id' => $booking->user->id,
                'name' => $booking->user->name,
                'email' => $booking->user->email,
                'phone' => $booking->user->phone
            ];
        }
        
        return $data;
    }
    
    private function getRussianStatus($status)
    {
        return match($status) {
            Booking::STATUS_ACTIVE => 'Активно',
            Booking::STATUS_CANCELLED => 'Отменено',
            Booking::STATUS_COMPLETED => 'Посещено',
            default => $status
        };
    }
}