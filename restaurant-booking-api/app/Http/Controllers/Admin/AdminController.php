<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'address' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
        ]);

        $restaurant = $this->adminService->createRestaurant($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Ресторан создан',
            'data' => $restaurant
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'capacity' => 'sometimes|integer|min:1',
        ]);

        $restaurant = $this->adminService->updateRestaurant($id, $data);
        
        return response()->json([
            'success' => true,
            'message' => 'Ресторан обновлен',
            'data' => $restaurant
        ]);
    }

    public function destroy($id)
    {
        $this->adminService->deleteRestaurant($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Ресторан удален'
        ]);
    }

    public function createTable(Request $request, $restaurantId)
    {
        $data = $request->validate([
            'table_number' => 'required|string|max:10',
            'capacity' => 'required|integer|min:1',
        ]);

        $table = $this->adminService->createTable($restaurantId, $data);
        
        return response()->json([
            'success' => true,
            'message' => 'Столик создан',
            'data' => $table
        ], 201);
    }

    public function updateTable(Request $request, $tableId)
    {
        $data = $request->validate([
            'table_number' => 'sometimes|string',
            'capacity' => 'sometimes|integer|min:1',
        ]);

        $table = $this->adminService->updateTable($tableId, $data);
        
        return response()->json([
            'success' => true,
            'message' => 'Столик обновлен',
            'data' => $table
        ]);
    }

    public function destroyTable($tableId)
    {
        $this->adminService->deleteTable($tableId);
        
        return response()->json([
            'success' => true,
            'message' => 'Столик удален'
        ]);
    }

    public function bookings(Request $request)
    {
        $bookings = $this->adminService->getAllBookings($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    public function cancelBooking($id)
    {
        $booking = $this->adminService->cancelBooking($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Бронь отменена',
            'data' => [
                'booking_id' => $booking->id,
                'status' => $booking->status
            ]
        ]);
    }
}