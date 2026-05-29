<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login validation.
     */
    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/login');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test successful login.
     */
    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'roles',
                    'permissions'
                ],
                'token'
            ]);
            
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test failed login.
     */
    public function test_user_cannot_login_with_incorrect_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422); // Or 401 depending on controller logic, usually 422 for validation or 401 for auth failure
        $this->assertGuest();
    }

    public function test_registered_user_gets_player_role(): void
    {
        $this->seed(RolesTableSeeder::class);

        $response = $this->postJson('/register', [
            'name' => 'Registered Player',
            'surname1' => 'Feature',
            'surname2' => 'Suite',
            'email' => 'registered-player@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated();

        $user = User::where('email', 'registered-player@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('player'));
        $this->assertFalse($user->hasRole('user'));
    }
}
