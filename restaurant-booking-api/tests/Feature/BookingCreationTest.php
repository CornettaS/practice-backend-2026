<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingCreationTest extends TestCase
{
    use RefreshDatabase;


    public function test_user_can_create_booking()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $token = $loginResponse->json('token');

        $restaurant = Restaurant::factory()->create([
            'opening_time' => '10:00:00',
            'closing_time' => '22:00:00'
        ]);
        
        $table = Table::factory()->create([
            'restaurant_id' => $restaurant->id,
            'capacity' => 4,
            'is_available' => true
        ]);

        $bookingData = [
            'table_id' => $table->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '18:30',
            'guests_count' => 3
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/bookings', $bookingData);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Бронь создана'
                 ])
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id',
                         'restaurant',
                         'date',
                         'time',
                         'guests',
                         'status'
                     ]
                 ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'table_id' => $table->id,
            'guests_count' => 3,
            'status' => 'active'
        ]);
    }

    public function test_cannot_create_booking_in_past()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $token = $loginResponse->json('token');

        $restaurant = Restaurant::factory()->create();
        $table = Table::factory()->create([
            'restaurant_id' => $restaurant->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/bookings', [
            'table_id' => $table->id,
            'booking_date' => now()->subDay()->format('Y-m-d'),
            'booking_time' => '18:00',
            'guests_count' => 2
        ]);

        $response->assertStatus(422);
    }


    public function test_cannot_create_booking_if_guests_exceed_capacity()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $token = $loginResponse->json('token');

        $restaurant = Restaurant::factory()->create();
        $table = Table::factory()->create([
            'restaurant_id' => $restaurant->id,
            'capacity' => 2 
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/bookings', [
            'table_id' => $table->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '18:00',
            'guests_count' => 4
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false
                 ]);
    }
}