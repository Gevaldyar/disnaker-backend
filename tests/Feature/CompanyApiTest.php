<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat admin untuk kebutuhan pengujian.
     */
    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);
    }

    /**
     * Membuat user perusahaan + profil perusahaan.
     */
    private function createCompany(
        string $email = 'company@test.com',
        string $name = 'PT Test Company',
        string $status = 'approved'
    ): array {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'password' => 'password123',
            'role' => 'perusahaan',
        ]);

        $company = Company::create([
            'user_id' => $user->id,
            'name' => $name,
            'description' => 'Deskripsi perusahaan.',
            'address' => 'Tasikmalaya',
            'phone' => '081234567890',
            'email' => $email,
            'website' => 'https://example.com',
            'logo' => null,
            'status' => $status,
            'rejection_reason' => null,
            'verified_at' => $status === 'approved'
                ? now()
                : null,
        ]);

        return [$user, $company];
    }

    public function test_company_can_register(): void
    {
        $response = $this->postJson('/api/company/register', [
            'name' => 'PT Perusahaan Baru',
            'email' => 'baru@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',

            'company_name' => 'PT Perusahaan Baru',
            'address' => 'Tasikmalaya',
            'description' => 'Perusahaan baru untuk testing.',
            'phone' => '081234567890',
            'company_email' => 'info@perusahaanbaru.com',
            'website' => 'https://perusahaanbaru.com',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'baru@test.com',
            'role' => 'perusahaan',
        ]);

        $user = User::where(
            'email',
            'baru@test.com'
        )->firstOrFail();

        $this->assertDatabaseHas('companies', [
            'user_id' => $user->id,
            'name' => 'PT Perusahaan Baru',
            'status' => 'pending',
        ]);
    }

    public function test_company_registration_rejects_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'company@test.com',
            'password' => 'password123',
            'role' => 'perusahaan',
        ]);

        $response = $this->postJson('/api/company/register', [
            'name' => 'PT Perusahaan Baru',
            'email' => 'company@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'PT Perusahaan Baru',
            'address' => 'Tasikmalaya',
        ]);

        $response->assertStatus(422);
    }

    public function test_pending_company_cannot_create_job(): void
    {
        [$companyUser, $company] = $this->createCompany(
            'pending@test.com',
            'PT Pending',
            'pending'
        );

        $response = $this
            ->actingAs($companyUser, 'sanctum')
            ->postJson('/api/company/jobs', [
                'title' => 'Staff Administrasi',
                'location' => 'Tasikmalaya',
                'description' => 'Deskripsi lowongan.',
                'expires_at' => now()
                    ->addDays(30)
                    ->format('Y-m-d'),
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('job_vacancies', [
            'company_id' => $company->id,
            'title' => 'Staff Administrasi',
        ]);
    }

    public function test_admin_can_approve_company(): void
    {
        [, $company] = $this->createCompany(
            'pending@test.com',
            'PT Pending',
            'pending'
        );

        $admin = $this->createAdmin();

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/admin/companies/{$company->id}/approve"
            );

        $response->assertStatus(200);

        $company->refresh();

        $this->assertEquals(
            'approved',
            $company->status
        );

        $this->assertNotNull(
            $company->verified_at
        );

        $this->assertNull(
            $company->rejection_reason
        );
    }

    public function test_approved_company_can_access_profile(): void
    {
        [$companyUser, $company] = $this->createCompany();

        $response = $this
            ->actingAs($companyUser, 'sanctum')
            ->getJson('/api/company/profile');

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.id',
                $company->id
            )
            ->assertJsonPath(
                'data.name',
                $company->name
            );
    }

    public function test_updating_company_profile_requires_reverification(): void
    {
        [$companyUser, $company] = $this->createCompany();

        $response = $this
            ->actingAs($companyUser, 'sanctum')
            ->putJson('/api/company/profile', [
                'name' => 'PT Test Company Updated',
                'description' => 'Data perusahaan diperbarui.',
                'address' => 'Tasikmalaya',
                'phone' => '081234567890',
                'email' => 'company@test.com',
                'website' => 'https://example.com',
            ]);

        $response->assertStatus(200);

        $company->refresh();

        $this->assertEquals(
            'pending',
            $company->status
        );

        $this->assertNull(
            $company->verified_at
        );

        $this->assertNull(
            $company->rejection_reason
        );
    }

    public function test_company_can_change_password(): void
    {
        [$companyUser] = $this->createCompany();

        $response = $this
            ->actingAs($companyUser, 'sanctum')
            ->patchJson('/api/company/password', [
                'current_password' => 'password123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(200);

        $companyUser->refresh();

        $this->assertTrue(
            Hash::check(
                'newpassword123',
                $companyUser->password
            )
        );
    }

    public function test_company_password_change_rejects_wrong_current_password(): void
    {
        [$companyUser] = $this->createCompany();

        $response = $this
            ->actingAs($companyUser, 'sanctum')
            ->patchJson('/api/company/password', [
                'current_password' => 'password-salah',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(422);

        $companyUser->refresh();

        $this->assertTrue(
            Hash::check(
                'password123',
                $companyUser->password
            )
        );
    }

    public function test_company_can_delete_account_with_correct_password(): void
    {
        Storage::fake('public');

        [$companyUser, $company] = $this->createCompany(
            'delete@test.com',
            'PT Delete Test'
        );

        $job = Job::create([
            'company_id' => $company->id,
            'title' => 'Job Delete Test',
            'poster' => 'job-posters/test.jpg',
            'location' => 'Tasikmalaya',
            'description' => 'Test delete account.',
            'published_at' => now(),
            'expires_at' => now()->addDays(30),
            'views' => 0,
            'status' => 'approved',
            'rejection_reason' => null,
            'verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($companyUser, 'sanctum')
            ->deleteJson('/api/company/account', [
                'password' => 'password123',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'id' => $companyUser->id,
        ]);

        $this->assertDatabaseMissing('companies', [
            'id' => $company->id,
        ]);

        $this->assertDatabaseMissing('job_vacancies', [
            'id' => $job->id,
        ]);
    }

    public function test_company_cannot_delete_account_with_wrong_password(): void
    {
        [$companyUser, $company] = $this->createCompany();

        $response = $this
            ->actingAs($companyUser, 'sanctum')
            ->deleteJson('/api/company/account', [
                'password' => 'password-salah',
            ]);

        $response->assertStatus(422);

        $this->assertDatabaseHas('users', [
            'id' => $companyUser->id,
        ]);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
        ]);
    }
}