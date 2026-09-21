<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat user perusahaan + profil perusahaan approved.
     */
    private function createApprovedCompany(
        string $email = 'company@test.com',
        string $name = 'PT Test Company'
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
            'description' => 'Perusahaan untuk testing.',
            'address' => 'Tasikmalaya',
            'phone' => '081234567890',
            'email' => $email,
            'website' => 'https://example.com',
            'status' => 'approved',
            'rejection_reason' => null,
            'verified_at' => now(),
        ]);

        return [$user, $company];
    }

    /**
     * Membuat admin untuk testing.
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
     * Membuat lowongan.
     */
    private function createJob(
        int $companyId,
        string $status = 'draft',
        ?string $expiresAt = null
    ): Job {
        return Job::create([
            'company_id' => $companyId,
            'title' => 'Staff Administrasi',
            'poster' => 'job-posters/test-poster.jpg',
            'location' => 'Tasikmalaya',
            'description' => 'Deskripsi lowongan untuk testing.',
            'published_at' => $status === 'approved'
                ? now()
                : null,
            'expires_at' => $expiresAt ?? now()->addDays(30),
            'views' => 0,
            'status' => $status,
            'rejection_reason' => null,
            'verified_at' => $status === 'approved'
                ? now()
                : null,
        ]);
    }

    public function test_public_api_only_shows_active_approved_jobs(): void
    {
        [, $company] = $this->createApprovedCompany();

        $this->createJob(
            $company->id,
            'approved',
            now()->addDays(10)
        );

        $this->createJob(
            $company->id,
            'pending',
            now()->addDays(10)
        );

        $this->createJob(
            $company->id,
            'draft',
            now()->addDays(10)
        );

        $response = $this->getJson('/api/jobs');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.title',
            'Staff Administrasi'
        );
    }

    public function test_expired_approved_job_is_not_shown_publicly(): void
    {
        [, $company] = $this->createApprovedCompany();

        $this->createJob(
            $company->id,
            'approved',
            now()->subDay()
        );

        $response = $this->getJson('/api/jobs');

        $response
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_public_job_detail_increments_views(): void
    {
        [, $company] = $this->createApprovedCompany();

        $job = $this->createJob(
            $company->id,
            'approved',
            now()->addDays(10)
        );

        $this->assertEquals(0, $job->views);

        $response = $this->getJson(
            "/api/jobs/{$job->id}"
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.id', $job->id)
            ->assertJsonPath('data.views', 1);

        $this->assertEquals(
            1,
            $job->fresh()->views
        );
    }

    public function test_approved_company_can_create_job(): void
    {
        Storage::fake('public');

        [$user, $company] = $this->createApprovedCompany();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->post('/api/company/jobs', [
                'title' => 'Digital Marketing',
                'poster' => UploadedFile::fake()->image(
                    'poster.jpg'
                ),
                'location' => 'Tasikmalaya',
                'description' => 'Lowongan Digital Marketing.',
                'expires_at' => now()
                    ->addDays(30)
                    ->format('Y-m-d'),
            ], [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('job_vacancies', [
            'company_id' => $company->id,
            'title' => 'Digital Marketing',
            'status' => 'draft',
        ]);
    }

    public function test_company_can_submit_own_draft_job(): void
    {
        [$user, $company] = $this->createApprovedCompany();

        $job = $this->createJob(
            $company->id,
            'draft',
            now()->addDays(10)
        );

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson(
                "/api/company/jobs/{$job->id}/submit"
            );

        $response->assertStatus(200);

        $this->assertDatabaseHas('job_vacancies', [
            'id' => $job->id,
            'status' => 'pending',
            'rejection_reason' => null,
        ]);
    }

    public function test_admin_can_approve_pending_job(): void
    {
        [, $company] = $this->createApprovedCompany();

        $admin = $this->createAdmin();

        $job = $this->createJob(
            $company->id,
            'pending',
            now()->addDays(10)
        );

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/admin/jobs/{$job->id}/approve"
            );

        $response->assertStatus(200);

        $this->assertDatabaseHas('job_vacancies', [
            'id' => $job->id,
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        $job->refresh();

        $this->assertNotNull($job->verified_at);
        $this->assertNotNull($job->published_at);
    }

    public function test_approved_job_becomes_visible_publicly_after_admin_approval(): void
    {
        [, $company] = $this->createApprovedCompany();

        $admin = $this->createAdmin();

        $job = $this->createJob(
            $company->id,
            'pending',
            now()->addDays(10)
        );

        // Sebelum approval.
        $before = $this->getJson('/api/jobs');

        $before
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');

        // Admin approve.
        $this
            ->actingAs($admin, 'sanctum')
            ->patchJson(
                "/api/admin/jobs/{$job->id}/approve"
            )
            ->assertStatus(200);

        // Setelah approval.
        $after = $this->getJson('/api/jobs');

        $after
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $after->assertJsonPath(
            'data.0.id',
            $job->id
        );
    }

    public function test_company_cannot_access_another_company_job(): void
    {
        [$companyUserA, $companyA] = $this->createApprovedCompany(
            'company-a@test.com',
            'PT Company A'
        );

        [, $companyB] = $this->createApprovedCompany(
            'company-b@test.com',
            'PT Company B'
        );

        $job = $this->createJob(
            $companyB->id,
            'draft',
            now()->addDays(10)
        );

        $response = $this
            ->actingAs($companyUserA, 'sanctum')
            ->getJson(
                "/api/company/jobs/{$job->id}"
            );

        $response->assertStatus(403);
    }

    public function test_company_cannot_update_another_company_job(): void
    {
        [$companyUserA] = $this->createApprovedCompany(
            'company-a@test.com',
            'PT Company A'
        );

        [, $companyB] = $this->createApprovedCompany(
            'company-b@test.com',
            'PT Company B'
        );

        $job = $this->createJob(
            $companyB->id,
            'draft',
            now()->addDays(10)
        );

        $response = $this
            ->actingAs($companyUserA, 'sanctum')
            ->putJson(
                "/api/company/jobs/{$job->id}",
                [
                    'title' => 'Lowongan Diubah',
                ]
            );

        $response->assertStatus(403);

        $this->assertDatabaseHas('job_vacancies', [
            'id' => $job->id,
            'title' => 'Staff Administrasi',
        ]);
    }

    public function test_company_cannot_delete_another_company_job(): void
    {
        [$companyUserA] = $this->createApprovedCompany(
            'company-a@test.com',
            'PT Company A'
        );

        [, $companyB] = $this->createApprovedCompany(
            'company-b@test.com',
            'PT Company B'
        );

        $job = $this->createJob(
            $companyB->id,
            'draft',
            now()->addDays(10)
        );

        $response = $this
            ->actingAs($companyUserA, 'sanctum')
            ->deleteJson(
                "/api/company/jobs/{$job->id}"
            );

        $response->assertStatus(403);

        $this->assertDatabaseHas('job_vacancies', [
            'id' => $job->id,
        ]);
    }
}