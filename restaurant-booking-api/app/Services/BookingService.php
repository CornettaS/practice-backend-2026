<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Table;
use Carbon\Carbon;

class BookingService
{
    private function timeToMinutes($time)
    {
        $parts = explode(':', $time);
        return (int)$parts[0] * 60 + (int)$parts[1];
    }

    public function checkAvailability($tableId, $date, $time, $guestsCount)
    {
        $table = Table::with('restaurant')->findOrFail($tableId);
        
        if ($table->capacity < $guestsCount) {
            return [
                'available' => false,
                'message' => "Столик максимум {$table->capacity} гостей"
            ];
        }

        $restaurant = $table->restaurant;
        
        $bookingTime = $time;
        $openingTime = substr($restaurant->opening_time, 0, 5); 
        $closingTime = substr($restaurant->closing_time, 0, 5); 
        
        $bookingMinutes = $this->timeToMinutes($bookingTime);
        $openingMinutes = $this->timeToMinutes($openingTime);
        $closingMinutes = $this->timeToMinutes($closingTime);
        
        if ($closingMinutes == 0) {
            $closingMinutes = 24 * 60; 
            
            $bookingEndMinutes = $bookingMinutes + 120;
            
            if ($bookingEndMinutes > 1440) {
                return [
                    'available' => false,
                    'message' => "Ресторан работает до полуночи. Нельзя бронировать после 22:00"
                ];
            }
        }
        
        if ($bookingMinutes < $openingMinutes || $bookingMinutes >= $closingMinutes) {
            $displayClosing = ($closingMinutes == 1440) ? '00:00' : $closingTime;
            return [
                'available' => false,
                'message' => "Ресторан работает с $openingTime до $displayClosing"
            ];
        }

        $bookingStart = $time . ':00';
        $bookingEnd = date('H:i:s', strtotime($bookingStart) + (2 * 3600));
        
        $existingBooking = Booking::where('table_id', $tableId)
            ->where('booking_date', $date)
            ->where('status', Booking::STATUS_ACTIVE)
            ->where(function($query) use ($bookingStart, $bookingEnd) {

                $query->whereBetween('booking_time', [$bookingStart, $bookingEnd])
                      ->orWhereRaw('ADDTIME(booking_time, "02:00:00") BETWEEN ? AND ?', [$bookingStart, $bookingEnd]);
            })
            ->exists();

        if ($existingBooking) {
            return [
                'available' => false,
                'message' => 'Это время уже занято'
            ];
        }

        return [
            'available' => true,
            'message' => 'Столик свободен'
        ];
    }

    public function getAvailableTables($restaurantId, $date, $time, $guestsCount)
    {
        $restaurant = \App\Models\Restaurant::find($restaurantId);
        
        if ($restaurant) {
            $openingTime = substr($restaurant->opening_time, 0, 5);
            $closingTime = substr($restaurant->closing_time, 0, 5);
            
            $bookingMinutes = $this->timeToMinutes($time);
            $openingMinutes = $this->timeToMinutes($openingTime);
            $closingMinutes = $this->timeToMinutes($closingTime);
            
            if ($closingMinutes == 0) {
                $closingMinutes = 1440;
            }
            
            if ($bookingMinutes < $openingMinutes || $bookingMinutes >= $closingMinutes) {
                return collect(); 
            }
            
            if ($bookingMinutes + 120 > $closingMinutes) {
                return collect(); 
            }
        }
        
        $tables = Table::where('restaurant_id', $restaurantId)
            ->where('capacity', '>=', $guestsCount)
            ->where('is_available', true)
            ->get();
            
        if ($tables->isEmpty()) {
            return collect();
        }
        
        $bookingStart = $time . ':00';
        $bookingEnd = date('H:i:s', strtotime($bookingStart) + (2 * 3600));
        
        $tableIds = $tables->pluck('id')->toArray();
        
        $busyTableIds = Booking::whereIn('table_id', $tableIds)
            ->where('booking_date', $date)
            ->where('status', Booking::STATUS_ACTIVE)
            ->where(function($query) use ($bookingStart, $bookingEnd) {
                $query->whereBetween('booking_time', [$bookingStart, $bookingEnd])
                      ->orWhereRaw('ADDTIME(booking_time, "02:00:00") BETWEEN ? AND ?', [$bookingStart, $bookingEnd]);
            })
            ->pluck('table_id')
            ->toArray();
        
        return $tables->whereNotIn('id', $busyTableIds)->values();
    }

    public function createBooking($userId, $data)
    {
        return Booking::create([
            'user_id' => $userId,
            'table_id' => $data['table_id'],
            'booking_date' => $data['booking_date'],
            'booking_time' => $data['booking_time'] . ':00',
            'guests_count' => $data['guests_count'],
            'status' => Booking::STATUS_ACTIVE,
        ]);
    }

    public function getUserBookings($userId, $type = 'all')
    {
        $query = Booking::with(['table.restaurant'])
            ->where('user_id', $userId);

        $now = Carbon::now();
        $currentDate = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');

        if ($type === 'upcoming') {
            $query->where('status', Booking::STATUS_ACTIVE)
                  ->where(function($q) use ($currentDate, $currentTime) {
                      $q->where('booking_date', '>', $currentDate)
                        ->orWhere(function($q2) use ($currentDate, $currentTime) {
                            $q2->where('booking_date', '=', $currentDate)
                               ->where('booking_time', '>', $currentTime);
                        });
                  });
        } 
        elseif ($type === 'history') {
            $query->where(function($q) use ($currentDate, $currentTime) {
                $q->whereIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_COMPLETED])
                  ->orWhere(function($q2) use ($currentDate, $currentTime) {
                      $q2->where('status', Booking::STATUS_ACTIVE)
                         ->where(function($q3) use ($currentDate, $currentTime) {
                             $q3->where('booking_date', '<', $currentDate)
                                ->orWhere(function($q4) use ($currentDate, $currentTime) {
                                    $q4->where('booking_date', '=', $currentDate)
                                       ->where('booking_time', '<=', $currentTime);
                                });
                         });
                  });
            });
        }

        return $query->orderBy('booking_date', 'desc')
                     ->orderBy('booking_time', 'desc')
                     ->get();
    }


    public function cancelOwnBooking($bookingId, $userId)
    {
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $userId)
            ->first();

        if (!$booking) {
            return [
                'success' => false,
                'message' => 'Бронь не найдена'
            ];
        }

        if ($booking->status !== Booking::STATUS_ACTIVE) {
            return [
                'success' => false,
                'message' => 'Бронь уже не активна'
            ];
        }

        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        return [
            'success' => true,
            'message' => 'Бронь отменена'
        ];
    }


    public function cancelAnyBooking($bookingId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return [
                'success' => false,
                'message' => 'Бронь не найдена'
            ];
        }

        if ($booking->status !== Booking::STATUS_ACTIVE) {
            return [
                'success' => false,
                'message' => 'Бронь уже не активна'
            ];
        }

        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        return [
            'success' => true,
            'message' => 'Бронь отменена'
        ];
    }

    public function completePastBookings()
    {
        $now = Carbon::now();
        $currentDate = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');
        
        Booking::where('status', Booking::STATUS_ACTIVE)
            ->where(function($query) use ($currentDate, $currentTime) {
                $query->where('booking_date', '<', $currentDate)
                    ->orWhere(function($q) use ($currentDate, $currentTime) {
                        $q->where('booking_date', '=', $currentDate)
                          ->where('booking_time', '<=', $currentTime);
                    });
            })
            ->update(['status' => Booking::STATUS_COMPLETED]);
    }
}