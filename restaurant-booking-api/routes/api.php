<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\Admin\AdminController;

// БЕЗ РЕГИСТРАЦИИ 
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/{id}', [RestaurantController::class, 'show']);
Route::get('/restaurants/{id}/reviews', [ReviewController::class, 'restaurantReviews']);

// АВТОРИЗАЦИЯ
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // БРОНЬ
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::patch('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::get('/bookings/upcoming', [BookingController::class, 'upcoming']);
    Route::get('/bookings/history', [BookingController::class, 'history']);
    
    // ОТЗЫВЫ
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::get('/my-reviews', [ReviewController::class, 'myReviews']);
    
    // ИЗБРАННОЕ
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{restaurantId}', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{restaurantId}', [FavoriteController::class, 'destroy']);
    Route::get('/favorites/check/{restaurantId}', [FavoriteController::class, 'check']);
    
    // АДМИНКА
    Route::prefix('admin')->group(function () {
        Route::post('/restaurants', [AdminController::class, 'store']);
        Route::put('/restaurants/{id}', [AdminController::class, 'update']);
        Route::delete('/restaurants/{id}', [AdminController::class, 'destroy']);
        Route::post('/restaurants/{id}/tables', [AdminController::class, 'createTable']);
        Route::get('/bookings', [AdminController::class, 'bookings']);
        Route::patch('/bookings/{id}/cancel', [AdminController::class, 'cancelBooking']);
    });
});