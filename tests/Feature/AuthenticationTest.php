<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin Disnaker',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'token',
            ],
        ]);

    $this->assertTrue($response->json('success'));
    $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_company_can_login(): void
    {
        $user = User::factory()->create([
            'name' => 'Perusahaan Test',
            'email' => 'company@test.com',
            'password' => Hash::make('password123'),
            'role' => 'perusahaan',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'company@test.com',
            'password' => 'password123',
        ]);

        $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'token',
            ],
        ]);

    $this->assertTrue($response->json('success'));
    $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_invalid_login_is_rejected(): void
    {
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'password-salah',
        ]);

        $response->assertStatus(401);
    }

    public function test_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_me(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/me');

        $response
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'email' => 'admin@test.com',
                'role' => 'admin',
            ],
        ]);
    }
}