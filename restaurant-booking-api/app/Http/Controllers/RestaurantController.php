<?php

namespace App\Http\Controllers;

use App\Services\RestaurantService;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    protected $restaurantService;

    public function __construct(RestaurantService $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }

    public function index()
    {
        return response()->json($this->restaurantService->getAllRestaurants());
    }

    public function show($id)
    {
        return response()->json($this->restaurantService->getRestaurantById($id));
    }
}