<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RestaurantService;
use App\Services\BookingService;
use App\Models\Table;
use App\Models\Booking;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $restaurantService;
    protected $bookingService;

    public function __construct(RestaurantService $restaurantService, BookingService $bookingService)
    {
        $this->restaurantService = $restaurantService;
        $this->bookingService = $bookingService;
        
        $this->middleware(function ($request, $next) {
            if ($request->user()->role !== 'admin') {
                return response()->json([
                    'message' => 'Доступ запрещен. Требуются права администратора.'
                ], 403);
            }
            return $next($request);
        });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
        ]);

        $restaurant = $this->restaurantService->createRestaurant($data);

        return response()->json([
            'message' => 'Ресторан успешно создан',
            'restaurant' => $restaurant
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'city_id' => 'sometimes|exists:cities,id',
            'address' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'opening_time' => 'sometimes|date_format:H:i',
            'closing_time' => 'sometimes|date_format:H:i|after:opening_time',
        ]);

        $restaurant = $this->restaurantService->updateRestaurant($id, $data);

        return response()->json([
            'message' => 'Ресторан успешно обновлен',
            'restaurant' => $restaurant
        ]);
    }

    public function destroy($id)
    {
        $this->restaurantService->deleteRestaurant($id);

        return response()->json([
            'message' => 'Ресторан успешно удален'
        ]);
    }

    public function createTable(Request $request, $restaurantId)
    {
        $request->validate([
            'table_number' => 'required|string|max:10',
            'capacity' => 'required|integer|min:1',
        ]);

        $table = Table::create([
            'restaurant_id' => $restaurantId,
            'table_number' => $request->table_number,
            'capacity' => $request->capacity,
            'is_available' => true
        ]);

        return response()->json([
            'message' => 'Столик успешно создан',
            'table' => $table
        ], 201);
    }

    public function bookings()
    {
        $bookings = Booking::with(['user', 'table.restaurant'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($bookings);
    }

    public function cancelBooking($id)
    {
        $result = $this->bookingService->cancelBooking($id, null, true);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 400);
        }

        return response()->json($result);
    }
}