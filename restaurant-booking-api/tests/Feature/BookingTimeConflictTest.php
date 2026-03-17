<?php
// tests/Feature/BookingTimeConflictTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingTimeConflictTest extends TestCase
{
    use RefreshDatabase; // Очищает БД перед каждым тестом

    /**
     * Тест: нельзя забронировать столик, если время пересекается с существующей бронью
     */
    public function test_cannot_book_table_if_time_conflicts()
    {
        // 1. СОЗДАЕМ ТЕСТОВЫЕ ДАННЫЕ
        $user = User::factory()->create(['role' => 'user']);
        $restaurant = Restaurant::factory()->create([
            'opening_time' => '10:00:00',
            'closing_time' => '22:00:00'
        ]);
        $table = Table::factory()->create([
            'restaurant_id' => $restaurant->id,
            'capacity' => 4,
            'is_available' => true
        ]);

        // 2. СОЗДАЕМ ПЕРВУЮ БРОНЬ (18:00 - 20:00)
        $firstBooking = Booking::create([
            'user_id' => $user->id,
            'table_id' => $table->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '18:00:00',
            'guests_count' => 2,
            'status' => Booking::STATUS_ACTIVE
        ]);

        // 3. ПЫТАЕМСЯ СОЗДАТЬ ВТОРУЮ БРОНЬ НА ТОТ ЖЕ СТОЛИК, НО НА 19:00 (ПЕРЕСЕЧЕНИЕ)
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password' // пароль по умолчанию в фабрике
        ]);
        
        $token = $response->json('token');

        $bookingData = [
            'table_id' => $table->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '19:00',
            'guests_count' => 2
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/bookings', $bookingData);

        // 4. ПРОВЕРЯЕМ: ДОЛЖНА БЫТЬ ОШИБКА
        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Это время уже занято'
                 ]);
    }

    /**
     * Тест: можно забронировать столик, если время не пересекается
     */
    public function test_can_book_table_if_time_is_free()
    {
        $user = User::factory()->create(['role' => 'user']);
        $restaurant = Restaurant::factory()->create([
            'opening_time' => '10:00:00',
            'closing_time' => '22:00:00'
        ]);
        $table = Table::factory()->create([
            'restaurant_id' => $restaurant->id,
            'capacity' => 4,
            'is_available' => true
        ]);

        $firstBooking = Booking::create([
            'user_id' => $user->id,
            'table_id' => $table->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '18:00:00',
            'guests_count' => 2,
            'status' => Booking::STATUS_ACTIVE
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $token = $response->json('token');

        $bookingData = [
            'table_id' => $table->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '20:30',
            'guests_count' => 2
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/bookings', $bookingData);

        // 4. ПРОВЕРЯЕМ: ДОЛЖЕН БЫТЬ УСПЕХ
        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Бронь создана'
                 ]);
    }
}