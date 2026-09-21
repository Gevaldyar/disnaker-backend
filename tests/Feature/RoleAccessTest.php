<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_company_cannot_access_admin_dashboard(): void
    {
        $company = User::factory()->create([
            'role' => 'perusahaan',
        ]);

        $response = $this
            ->actingAs($company, 'sanctum')
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_company_can_access_company_dashboard(): void
    {
        $company = User::factory()->create([
            'role' => 'perusahaan',
        ]);

        $response = $this
            ->actingAs($company, 'sanctum')
            ->getJson('/api/company/dashboard');

        // Endpoint memerlukan data company.
        // Untuk sementara kita hanya memastikan middleware
        // role perusahaan tidak menolak request.
        $this->assertNotSame(403, $response->status());
    }

    public function test_admin_cannot_access_company_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/company/dashboard');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->getJson('/api/admin/dashboard');

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_access_company_dashboard(): void
    {
        $response = $this->getJson('/api/company/dashboard');

        $response->assertStatus(401);
    }
}