<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Table;
class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }
    
 
    private function checkBookingAccess($booking, $user)
    {
        if ($booking->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Доступ запрещен');
        }
    }

    public function index(Request $request)
    {
        $bookings = $this->bookingService->getUserBookings($request->user()->id);
        
        return response()->json([
            'success' => true,
            'data' => $bookings->map(fn($b) => [
                'id' => $b->id,
                'restaurant' => $b->table->restaurant->name,
                'restaurant_id' => $b->table->restaurant->id,
                'address' => $b->table->restaurant->address,
                'date' => $b->booking_date,
                'time' => substr($b->booking_time, 0, 5),
                'end_time' => date('H:i', strtotime($b->booking_time) + (2 * 3600)),
                'guests' => $b->guests_count,
                'table_number' => $b->table->table_number,
                'table_id' => $b->table->id,
                'status' => $b->status
            ])
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'guests_count' => 'required|integer|min:1'
        ]);

        $check = $this->bookingService->checkAvailability(
            $data['table_id'],
            $data['booking_date'],
            $data['booking_time'],
            $data['guests_count']
        );

        if (!$check['available']) {
            return response()->json([
                'success' => false,
                'message' => $check['message']
            ], 400);
        }

        $booking = $this->bookingService->createBooking($request->user()->id, $data);
        $booking->load('table.restaurant');

        return response()->json([
            'success' => true,
            'message' => 'Бронирование успешно создано',
            'data' => [
                'id' => $booking->id,
                'restaurant' => $booking->table->restaurant->name,
                'restaurant_id' => $booking->table->restaurant->id,
                'address' => $booking->table->restaurant->address,
                'date' => $booking->booking_date,
                'time' => substr($booking->booking_time, 0, 5),
                'end_time' => date('H:i', strtotime($booking->booking_time) + (2 * 3600)),
                'guests' => $booking->guests_count,
                'table_number' => $booking->table->table_number,
                'table_id' => $booking->table->id,
                'status' => $booking->status
            ]
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $booking = Booking::with(['table.restaurant', 'user'])->findOrFail($id);
        
        $this->checkBookingAccess($booking, $request->user());

        return response()->json([
            'success' => true,
            'data' => [
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
                'date' => $booking->booking_date,
                'time' => substr($booking->booking_time, 0, 5),
                'end_time' => date('H:i', strtotime($booking->booking_time) + (2 * 3600)),
                'guests' => $booking->guests_count,
                'status' => $booking->status,
                'created_at' => $booking->created_at,
                'user' => [
                    'id' => $booking->user->id,
                    'name' => $booking->user->name,
                    'email' => $booking->user->email,
                    'phone' => $booking->user->phone
                ]
            ]
        ]);
    }

    public function cancel(Request $request, $id)
        {
            $booking = Booking::findOrFail($id);
            
            $this->checkBookingAccess($booking, $request->user());
            
            if ($request->user()->role === 'admin') {
                $result = $this->bookingService->cancelAnyBooking($id);
            } else {
                $result = $this->bookingService->cancelOwnBooking($id, $request->user()->id);
            }

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Бронь отменена'
            ]);
        }

    public function upcoming(Request $request)
    {
        $bookings = $this->bookingService->getUserBookings($request->user()->id, 'upcoming');
        
        return response()->json([
            'success' => true,
            'data' => $bookings->map(fn($b) => [
                'id' => $b->id,
                'restaurant' => $b->table->restaurant->name,
                'restaurant_id' => $b->table->restaurant->id,
                'address' => $b->table->restaurant->address,
                'date' => $b->booking_date,
                'time' => substr($b->booking_time, 0, 5),
                'end_time' => date('H:i', strtotime($b->booking_time) + (2 * 3600)),
                'guests' => $b->guests_count,
                'table_number' => $b->table->table_number,
                'table_id' => $b->table->id,
                'status' => $b->status
            ])
        ]);
    }

    public function history(Request $request)
    {
        $bookings = $this->bookingService->getUserBookings($request->user()->id, 'history');
        
        return response()->json([
            'success' => true,
            'data' => $bookings->map(fn($b) => [
                'id' => $b->id,
                'restaurant' => $b->table->restaurant->name,
                'restaurant_id' => $b->table->restaurant->id,
                'address' => $b->table->restaurant->address,
                'date' => $b->booking_date,
                'time' => substr($b->booking_time, 0, 5),
                'end_time' => date('H:i', strtotime($b->booking_time) + (2 * 3600)),
                'guests' => $b->guests_count,
                'table_number' => $b->table->table_number,
                'table_id' => $b->table->id,
                'status' => $b->status,
                'status_text' => $b->status === 'cancelled' ? 'Отменено' : 
                                ($b->status === 'completed' ? 'Посещено' : 'Активно')
            ])
        ]);
    }

 
}