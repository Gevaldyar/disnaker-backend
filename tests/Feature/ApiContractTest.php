<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiContractTest extends TestCase
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

    private function createApprovedCompany(): array
    {
        $user = User::factory()->create([
            'name' => 'PT Contract Test',
            'email' => 'company@test.com',
            'password' => 'password123',
            'role' => 'perusahaan',
        ]);

        $company = Company::create([
            'user_id' => $user->id,
            'name' => 'PT Contract Test',
            'description' => 'Perusahaan testing.',
            'address' => 'Tasikmalaya',
            'phone' => '081234567890',
            'email' => 'company@test.com',
            'website' => 'https://example.com',
            'logo' => null,
            'status' => 'approved',
            'rejection_reason' => null,
            'verified_at' => now(),
        ]);

        return [$user, $company];
    }

    private function createApprovedJob(int $companyId): Job
    {
        return Job::create([
            'company_id' => $companyId,
            'title' => 'Staff Administrasi',
            'poster' => 'job-posters/test.jpg',
            'location' => 'Tasikmalaya',
            'description' => 'Deskripsi lowongan.',
            'published_at' => now(),
            'expires_at' => now()->addDays(30),
            'views' => 0,
            'status' => 'approved',
            'rejection_reason' => null,
            'verified_at' => now(),
        ]);
    }

    public function test_public_job_response_has_stable_structure(): void
    {
        [, $company] = $this->createApprovedCompany();

        $job = $this->createApprovedJob($company->id);

        $response = $this->getJson('/api/jobs');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'poster_url',
                        'location',
                        'description',
                        'published_at',
                        'expires_at',
                        'views',
                        'status',
                        'company' => [
                            'id',
                            'name',
                            'address',
                            'website',
                        ],
                    ],
                ],
                'links',
                'meta',
            ]);

        $response->assertJsonPath(
            'data.0.id',
            $job->id
        );
    }

    public function test_public_job_detail_has_stable_structure(): void
    {
        [, $company] = $this->createApprovedCompany();

        $job = $this->createApprovedJob($company->id);

        $response = $this->getJson(
            "/api/jobs/{$job->id}"
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'poster_url',
                    'location',
                    'description',
                    'published_at',
                    'expires_at',
                    'views',
                    'status',
                    'company' => [
                        'id',
                        'name',
                        'address',
                        'website',
                    ],
                ],
            ]);

        $response->assertJsonPath(
            'data.id',
            $job->id
        );
    }

    public function test_company_dashboard_response_has_stable_structure(): void
    {
        [$user, $company] = $this->createApprovedCompany();

        $job = $this->createApprovedJob($company->id);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/company/dashboard');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'company' => [
                        'id',
                        'name',
                        'status',
                        'rejection_reason',
                        'verified_at',
                    ],

                    'statistics' => [
                        'total_jobs',
                        'active_jobs',
                        'draft_jobs',
                        'pending_jobs',
                        'approved_jobs',
                        'rejected_jobs',
                        'expired_jobs',
                    ],

                    'latest_jobs',
                ],
            ]);

        $response->assertJsonPath(
            'data.statistics.total_jobs',
            1
        );

        $response->assertJsonPath(
            'data.latest_jobs.0.id',
            $job->id
        );
    }

    public function test_admin_dashboard_response_has_stable_structure(): void
    {
        $admin = $this->createAdmin();

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/dashboard');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'companies',
                    'jobs',
                    'job_seekers',
                    'content',
                    'pending_companies',
                    'pending_jobs',
                    'latest_news',
                ],
            ]);
    }

    public function test_company_registration_validation_uses_standard_error_structure(): void
    {
        $response = $this->postJson(
            '/api/company/register',
            []
        );

        $response
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    public function test_public_draft_job_is_not_exposed(): void
    {
        [, $company] = $this->createApprovedCompany();

        $job = Job::create([
            'company_id' => $company->id,
            'title' => 'Draft Job',
            'poster' => null,
            'location' => 'Tasikmalaya',
            'description' => 'Draft.',
            'published_at' => null,
            'expires_at' => now()->addDays(30),
            'views' => 0,
            'status' => 'draft',
            'rejection_reason' => null,
            'verified_at' => null,
        ]);

        $listResponse = $this->getJson('/api/jobs');

        $listResponse
            ->assertStatus(200)
            ->assertJsonMissing([
                'id' => $job->id,
            ]);

        $detailResponse = $this->getJson(
            "/api/jobs/{$job->id}"
        );

        $detailResponse->assertStatus(404);
    }

    public function test_public_statistics_only_returns_aggregate_data(): void
    {
        $response = $this->getJson(
            '/api/employment-statistics'
        );

        $response->assertStatus(200);

        $json = $response->json();

        $this->assertIsArray($json);

        $this->assertArrayHasKey(
            'data',
            $json
        );
    }
}