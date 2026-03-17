<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_cancel_own_booking()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $token = $loginResponse->json('token');

        $restaurant = Restaurant::factory()->create();
        $table = Table::factory()->create(['restaurant_id' => $restaurant->id]);
        
        $booking = Booking::create([
            'user_id' => $user->id,
            'table_id' => $table->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '18:00:00',
            'guests_count' => 2,
            'status' => Booking::STATUS_ACTIVE
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->patchJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Бронь отменена'
                 ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => Booking::STATUS_CANCELLED
        ]);
    }

    public function test_user_cannot_cancel_others_booking()
    {
        // Создаем двух пользователей
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);
        
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user2->email,
            'password' => 'password'
        ]);
        
        $token = $loginResponse->json('token');

        $restaurant = Restaurant::factory()->create();
        $table = Table::factory()->create(['restaurant_id' => $restaurant->id]);
        
        $booking = Booking::create([
            'user_id' => $user1->id, 
            'table_id' => $table->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '18:00:00',
            'guests_count' => 2,
            'status' => Booking::STATUS_ACTIVE
        ]);

    
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->patchJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(403);
    }
}