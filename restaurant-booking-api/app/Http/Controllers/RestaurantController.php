<?php

namespace App\Http\Controllers;

use App\Services\RestaurantService;
use App\Services\BookingService;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    protected $restaurantService;
    protected $bookingService;

    public function __construct(
        RestaurantService $restaurantService,
        BookingService $bookingService
    ) {
        $this->restaurantService = $restaurantService;
        $this->bookingService = $bookingService;
    }


    public function index(Request $request)
    {
        $filters = $request->only(['city_id', 'capacity', 'search']);
        $restaurants = $this->restaurantService->getAllRestaurants($filters);
        
        return response()->json([
            'success' => true,
            'data' => $restaurants
        ]);
    }


    public function show($id)
    {
        $restaurant = $this->restaurantService->getRestaurantById($id);
        
        return response()->json([
            'success' => true,
            'data' => $restaurant
        ]);
    }
    

    public function availableTables(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'guests' => 'required|integer|min:1'
        ]);
        
        $tables = $this->bookingService->getAvailableTables(
            $id,
            $request->date,
            $request->time,
            $request->guests
        );
        
        return response()->json([
            'success' => true,
            'data' => $tables->map(function($table) {
                return [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'capacity' => $table->capacity,
                    'restaurant_id' => $table->restaurant_id
                ];
            })
        ]);
    }
    

    public function schedule(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date'
        ]);
        
        $schedule = $this->restaurantService->getSchedule($id, $request->date);
        
        return response()->json([
            'success' => true,
            'data' => $schedule
        ]);
    }
}