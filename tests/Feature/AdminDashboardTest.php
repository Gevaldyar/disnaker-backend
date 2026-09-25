<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use App\Notifications\AdminPendingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Dashboard Test',
            'email' => 'admin-dashboard@test.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $companyUser = User::factory()->create([
            'name' => 'Company Dashboard Test',
            'email' => 'company-dashboard@test.com',
            'password' => 'password123',
            'role' => 'perusahaan',
        ]);

        $company = Company::create([
            'user_id' => $companyUser->id,
            'name' => 'PT Dashboard Test',
            'description' => 'Company dashboard test.',
            'address' => 'Tasikmalaya',
            'phone' => '081234567890',
            'email' => 'dashboard@test.com',
            'website' => 'https://example.com',
            'logo' => null,
            'status' => 'pending',
            'rejection_reason' => null,
            'verified_at' => null,
        ]);

        Job::create([
            'company_id' => $company->id,
            'title' => 'Staff Dashboard Test',
            'poster' => null,
            'location' => 'Tasikmalaya',
            'description' => 'Dashboard test.',
            'published_at' => null,
            'expires_at' => now()->addDays(30),
            'views' => 0,
            'status' => 'pending',
            'rejection_reason' => null,
            'verified_at' => null,
        ]);

        $admin->notify(
            new AdminPendingNotification(
                'company_registered',
                'Perusahaan Baru',
                'Ada perusahaan baru yang mendaftar dan menunggu verifikasi.',
                $company->id,
                null
            )
        );

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/dashboard');

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'success',
                true
            )
            ->assertJsonPath(
                'data.companies.pending',
                1
            )
            ->assertJsonPath(
                'data.jobs.pending',
                1
            )
            ->assertJsonPath(
                'data.notifications.unread',
                1
            );
    }

    public function test_company_cannot_access_admin_dashboard(): void
    {
        $companyUser = User::factory()->create([
            'name' => 'Company Dashboard Forbidden',
            'email' => 'company-dashboard-forbidden@test.com',
            'password' => 'password123',
            'role' => 'perusahaan',
        ]);

        $response = $this
            ->actingAs($companyUser, 'sanctum')
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(403);
    }
}