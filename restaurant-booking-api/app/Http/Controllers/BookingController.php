<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $bookings = $this->bookingService->getUserBookings($request->user()->id);
        
        return response()->json([
            'success' => true,
            'data' => $bookings->map(fn($b) => [
                'id' => $b->id,
                'restaurant' => $b->table->restaurant->name,
                'address' => $b->table->restaurant->address,
                'date' => $b->booking_date,
                'time' => substr($b->booking_time, 0, 5),
                'guests' => $b->guests_count,
                'table_number' => $b->table->table_number,
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
                'booking_id' => $booking->id,
                'restaurant' => $booking->table->restaurant->name,
                'address' => $booking->table->restaurant->address,
                'date' => $booking->booking_date,
                'time' => substr($booking->booking_time, 0, 5),
                'guests' => $booking->guests_count,
                'table_number' => $booking->table->table_number,
                'status' => $booking->status
            ]
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $booking = \App\Models\Booking::with(['table.restaurant', 'user'])->findOrFail($id);

        if ($booking->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Доступ запрещен'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $booking->id,
                'restaurant' => [
                    'name' => $booking->table->restaurant->name,
                    'address' => $booking->table->restaurant->address,
                ],
                'table' => [
                    'number' => $booking->table->table_number,
                    'capacity' => $booking->table->capacity
                ],
                'date' => $booking->booking_date,
                'time' => substr($booking->booking_time, 0, 5),
                'guests' => $booking->guests_count,
                'status' => $booking->status,
                'user' => [
                    'name' => $booking->user->name,
                    'email' => $booking->user->email,
                    'phone' => $booking->user->phone
                ]
            ]
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $result = $this->bookingService->cancelBooking(
            $id,
            $request->user()->id,
            $request->user()->role === 'admin'
        );

        if (!$result['success']) {
            return response()->json($result, $result['code'] ?? 400);
        }

        return response()->json($result);
    }

    public function upcoming(Request $request)
    {
        $bookings = $this->bookingService->getUserBookings($request->user()->id, 'upcoming');
        
        return response()->json([
            'success' => true,
            'data' => $bookings->map(fn($b) => [
                'id' => $b->id,
                'restaurant' => $b->table->restaurant->name,
                'address' => $b->table->restaurant->address,
                'date' => $b->booking_date,
                'time' => substr($b->booking_time, 0, 5),
                'guests' => $b->guests_count,
                'table_number' => $b->table->table_number,
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
                'address' => $b->table->restaurant->address,
                'date' => $b->booking_date,
                'time' => substr($b->booking_time, 0, 5),
                'guests' => $b->guests_count,
                'status' => $b->status === 'cancelled' ? 'Отменено' : 'Посещено'
            ])
        ]);
    }
}