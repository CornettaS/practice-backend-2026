<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()->favoriteRestaurants()
            ->with(['city.country'])
            ->paginate(15);

        return response()->json([
            'total' => $favorites->total(),
            'favorites' => $favorites
        ]);
    }

    public function store(Request $request, $restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        
        $exists = $request->user()->favoriteRestaurants()
            ->where('restaurant_id', $restaurantId)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Ресторан уже в избранном'
            ], 400);
        }

        $request->user()->favoriteRestaurants()->attach($restaurantId);

        return response()->json([
            'message' => 'Ресторан добавлен в избранное',
            'restaurant' => $restaurant->load('city.country') 
        ]);
    }


    public function destroy(Request $request, $restaurantId)
    {
        Restaurant::findOrFail($restaurantId);
        
        $request->user()->favoriteRestaurants()->detach($restaurantId);

        return response()->json([
            'message' => 'Ресторан удален из избранного'
        ]);
    }

    public function check(Request $request, $restaurantId)
    {
        Restaurant::findOrFail($restaurantId);
        
        $isFavorite = $request->user()->favoriteRestaurants()
            ->where('restaurant_id', $restaurantId)
            ->exists();

        return response()->json([
            'restaurant_id' => $restaurantId,
            'is_favorite' => $isFavorite
        ]);
    }

    public function popular()
    {
        $popularRestaurants = Restaurant::withCount('favoritedBy')
            ->with(['city.country'])
            ->orderBy('favorited_by_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json($popularRestaurants);
    }
}