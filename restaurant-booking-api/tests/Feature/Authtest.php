<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_create_booking_without_token()
    {
        $response = $this->postJson('/api/bookings', [
            'table_id' => 1,
            'booking_date' => '2026-03-20',
            'booking_time' => '19:00',
            'guests_count' => 2
        ]);

        $response->assertStatus(401); 
    }

    public function test_can_create_booking_with_token()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $token = $loginResponse->json('token');

        $restaurant = Restaurant::factory()->create();
        $table = \App\Models\Table::factory()->create([
            'restaurant_id' => $restaurant->id,
            'capacity' => 4
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/bookings', [
            'table_id' => $table->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '19:00',
            'guests_count' => 2
        ]);

        $response->assertStatus(201);
    }

 
    public function test_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $token = $loginResponse->json('token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/restaurants', [
            'name' => 'Новый ресторан',
            'city_id' => 1,
            'address' => 'ул. Тестовая',
            'capacity' => 100,
            'opening_time' => '10:00',
            'closing_time' => '22:00'
        ]);

        $response->assertStatus(403); 
    }


    public function test_admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $loginResponse = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'password'
        ]);
        
        $token = $loginResponse->json('token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/restaurants', [
            'name' => 'Новый ресторан',
            'city_id' => 1,
            'address' => 'ул. Тестовая',
            'capacity' => 100,
            'opening_time' => '10:00',
            'closing_time' => '22:00'
        ]);

        $response->assertStatus(201);
    }
}