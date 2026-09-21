<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Job;
use App\Models\JobSeeker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);
    }

    private function createCompany(
        string $email,
        string $name,
        string $status = 'pending'
    ): Company {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'password' => 'password123',
            'role' => 'perusahaan',
        ]);

        return Company::create([
            'user_id' => $user->id,
            'name' => $name,
            'description' => 'Perusahaan test.',
            'address' => 'Tasikmalaya',
            'phone' => '081234567890',
            'email' => $email,
            'website' => 'https://example.com',
            'logo' => null,
            'status' => $status,
            'rejection_reason' => null,
            'verified_at' => null,
        ]);
    }

    private function createJob(
        int $companyId,
        string $status = 'pending'
    ): Job {
        return Job::create([
            'company_id' => $companyId,
            'title' => 'Staff Administrasi',
            'poster' => 'job-posters/test.jpg',
            'location' => 'Tasikmalaya',
            'description' => 'Deskripsi lowongan test.',
            'published_at' => null,
            'expires_at' => now()->addDays(30),
            'views' => 0,
            'status' => $status,
            'rejection_reason' => null,
            'verified_at' => null,
        ]);
    }

    private function createJobSeeker(
        string $nik = '3278000000000001',
        string $status = 'pending'
    ): JobSeeker {
        return JobSeeker::create([
            'ak1_number' => 'AK1-TEST-' . rand(1000, 9999),
            'nik' => $nik,
            'name' => 'Budi Test',
            'birth_place' => 'Tasikmalaya',
            'birth_date' => '2002-05-10',
            'gender' => 'Laki-laki',
            'marital_status' => 'Belum Menikah',
            'address' => 'Tasikmalaya',
            'phone' => '081234567890',
            'email' => 'budi@test.com',
            'last_education' => 'SMA',
            'institution' => 'SMAN 1 Tasikmalaya',
            'skills' => 'Microsoft Office',
            'languages' => 'Bahasa Indonesia',
            'desired_position' => 'Staff Administrasi',
            'desired_location' => 'Tasikmalaya',
            'desired_salary' => 3000000,
            'worked_last_6_months' => false,
            'status' => $status,
            'rejection_reason' => null,
            'verified_at' => null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | COMPANY
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_approve_company(): void
    {
        $admin = $this->createAdmin();

        $company = $this->createCompany(
            'company@test.com',
            'PT Test Company'
        );

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

    public function test_admin_can_reject_company(): void
    {
        $admin = $this->createAdmin();

        $company = $this->createCompany(
            'company@test.com',
            'PT Test Company'
        );

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/admin/companies/{$company->id}/reject",
                [
                    'rejection_reason' =>
                        'Dokumen perusahaan belum lengkap.',
                ]
            );

        $response->assertStatus(200);

        $company->refresh();

        $this->assertEquals(
            'rejected',
            $company->status
        );

        $this->assertEquals(
            'Dokumen perusahaan belum lengkap.',
            $company->rejection_reason
        );

        $this->assertNull(
            $company->verified_at
        );
    }

    public function test_reject_company_requires_reason(): void
    {
        $admin = $this->createAdmin();

        $company = $this->createCompany(
            'company@test.com',
            'PT Test Company'
        );

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/admin/companies/{$company->id}/reject"
            );

        $response->assertStatus(422);

        $company->refresh();

        $this->assertEquals(
            'pending',
            $company->status
        );
    }

    public function test_admin_can_suspend_company(): void
    {
        $admin = $this->createAdmin();

        $company = $this->createCompany(
            'company@test.com',
            'PT Test Company',
            'approved'
        );

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/admin/companies/{$company->id}/suspend"
            );

        $response->assertStatus(200);

        $company->refresh();

        $this->assertEquals(
            'suspended',
            $company->status
        );
    }

    /*
    |--------------------------------------------------------------------------
    | JOBS
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_approve_job(): void
    {
        $admin = $this->createAdmin();

        $company = $this->createCompany(
            'company@test.com',
            'PT Test Company',
            'approved'
        );

        $job = $this->createJob(
            $company->id,
            'pending'
        );

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/admin/jobs/{$job->id}/approve"
            );

        $response->assertStatus(200);

        $job->refresh();

        $this->assertEquals(
            'approved',
            $job->status
        );

        $this->assertNotNull(
            $job->published_at
        );

        $this->assertNotNull(
            $job->verified_at
        );

        $this->assertNull(
            $job->rejection_reason
        );
    }

    public function test_admin_can_reject_job(): void
    {
        $admin = $this->createAdmin();

        $company = $this->createCompany(
            'company@test.com',
            'PT Test Company',
            'approved'
        );

        $job = $this->createJob(
            $company->id,
            'pending'
        );

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/admin/jobs/{$job->id}/reject",
                [
                    'rejection_reason' =>
                        'Informasi lowongan belum lengkap.',
                ]
            );

        $response->assertStatus(200);

        $job->refresh();

        $this->assertEquals(
            'rejected',
            $job->status
        );

        $this->assertEquals(
            'Informasi lowongan belum lengkap.',
            $job->rejection_reason
        );

        $this->assertNull(
            $job->published_at
        );
    }

    public function test_reject_job_requires_reason(): void
    {
        $admin = $this->createAdmin();

        $company = $this->createCompany(
            'company@test.com',
            'PT Test Company',
            'approved'
        );

        $job = $this->createJob(
            $company->id,
            'pending'
        );

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/admin/jobs/{$job->id}/reject"
            );

        $response->assertStatus(422);

        $job->refresh();

        $this->assertEquals(
            'pending',
            $job->status
        );
    }

    public function test_admin_can_delete_job(): void
    {
        $admin = $this->createAdmin();

        $company = $this->createCompany(
            'company@test.com',
            'PT Test Company',
            'approved'
        );

        $job = $this->createJob(
            $company->id,
            'approved'
        );

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->deleteJson(
                "/api/admin/jobs/{$job->id}"
            );

        $response->assertStatus(200);

        $this->assertDatabaseMissing('job_vacancies', [
            'id' => $job->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | JOB SEEKERS
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_verify_job_seeker(): void
    {
        $admin = $this->createAdmin();

        $jobSeeker = $this->createJobSeeker();

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/admin/job-seekers/{$jobSeeker->id}/verify"
            );

        $response->assertStatus(200);

        $jobSeeker->refresh();

        $this->assertEquals(
            'verified',
            $jobSeeker->status
        );

        $this->assertNotNull(
            $jobSeeker->verified_at
        );

        $this->assertNull(
            $jobSeeker->rejection_reason
        );
    }

    public function test_admin_can_reject_job_seeker(): void
    {
        $admin = $this->createAdmin();

        $jobSeeker = $this->createJobSeeker(
            '3278000000000002'
        );

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/admin/job-seekers/{$jobSeeker->id}/reject",
                [
                    'rejection_reason' =>
                        'Data pencari kerja belum lengkap.',
                ]
            );

        $response->assertStatus(200);

        $jobSeeker->refresh();

        $this->assertEquals(
            'rejected',
            $jobSeeker->status
        );

        $this->assertEquals(
            'Data pencari kerja belum lengkap.',
            $jobSeeker->rejection_reason
        );

        $this->assertNotNull(
            $jobSeeker->verified_at
        );
    }

    public function test_reject_job_seeker_requires_reason(): void
    {
        $admin = $this->createAdmin();

        $jobSeeker = $this->createJobSeeker(
            '3278000000000003'
        );

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/admin/job-seekers/{$jobSeeker->id}/reject"
            );

        $response->assertStatus(422);

        $jobSeeker->refresh();

        $this->assertEquals(
            'pending',
            $jobSeeker->status
        );
    }

    public function test_non_admin_cannot_manage_company(): void
    {
        $companyUser = User::factory()->create([
            'email' => 'company@test.com',
            'password' => 'password123',
            'role' => 'perusahaan',
        ]);

        $company = $this->createCompany(
            'other-company@test.com',
            'PT Other Company'
        );

        $response = $this
            ->actingAs($companyUser, 'sanctum')
            ->patchJson(
                "/api/admin/companies/{$company->id}/approve"
            );

        $response->assertStatus(403);

        $company->refresh();

        $this->assertEquals(
            'pending',
            $company->status
        );
    }

    public function test_non_admin_cannot_manage_job(): void
    {
        $companyUser = User::factory()->create([
            'email' => 'company@test.com',
            'password' => 'password123',
            'role' => 'perusahaan',
        ]);

        $company = $this->createCompany(
            'other-company@test.com',
            'PT Other Company',
            'approved'
        );

        $job = $this->createJob(
            $company->id,
            'pending'
        );

        $response = $this
            ->actingAs($companyUser, 'sanctum')
            ->patchJson(
                "/api/admin/jobs/{$job->id}/approve"
            );

        $response->assertStatus(403);

        $job->refresh();

        $this->assertEquals(
            'pending',
            $job->status
        );
    }

    public function test_non_admin_cannot_manage_job_seeker(): void
    {
        $companyUser = User::factory()->create([
            'email' => 'company@test.com',
            'password' => 'password123',
            'role' => 'perusahaan',
        ]);

        $jobSeeker = $this->createJobSeeker(
            '3278000000000004'
        );

        $response = $this
            ->actingAs($companyUser, 'sanctum')
            ->patchJson(
                "/api/admin/job-seekers/{$jobSeeker->id}/verify"
            );

        $response->assertStatus(403);

        $jobSeeker->refresh();

        $this->assertEquals(
            'pending',
            $jobSeeker->status
        );
    }
}