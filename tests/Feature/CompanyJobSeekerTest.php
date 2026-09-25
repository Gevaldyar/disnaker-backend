<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\JobSeekerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyJobSeekerTest extends TestCase
{
    use RefreshDatabase;

    private function createApprovedCompany(): User
    {
        $user = User::factory()->create([
            'name' => 'Company Test',
            'email' => 'company@test.com',
            'password' => 'password123',
            'role' => 'perusahaan',
        ]);

        Company::create([
            'user_id' => $user->id,
            'name' => 'PT Test Company',
            'address' => 'Tasikmalaya',
            'phone' => '081234567890',
            'email' => 'company@test.com',
            'status' => 'approved',
            'verified_at' => now(),
        ]);

        return $user;
    }

    private function createJobSeeker(
        bool $public = true
    ): JobSeekerProfile {
        $user = User::factory()->create([
            'name' => 'Budi Test',
            'email' => 'budi@test.com',
            'password' => 'password123',
            'role' => 'pencari_kerja',
        ]);

        return JobSeekerProfile::create([
            'user_id' => $user->id,
            'full_name' => 'Budi Test',
            'headline' => 'Laravel Developer',
            'bio' => 'Web developer.',
            'phone' => '081234567890',
            'city' => 'Tasikmalaya',
            'is_public' => $public,
        ]);
    }

    public function test_approved_company_can_view_public_job_seeker(): void
    {
        $company = $this->createApprovedCompany();

        $profile = $this->createJobSeeker();

        $response = $this
            ->actingAs($company, 'sanctum')
            ->getJson(
                "/api/company/job-seekers/{$profile->id}"
            );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.full_name',
                'Budi Test'
            )
            ->assertJsonPath(
                'data.phone',
                '081234567890'
            );
    }

    public function test_pending_company_cannot_view_job_seeker(): void
    {
        $user = User::factory()->create([
            'name' => 'Pending Company',
            'email' => 'pending-company@test.com',
            'password' => 'password123',
            'role' => 'perusahaan',
        ]);

        Company::create([
            'user_id' => $user->id,
            'name' => 'PT Pending Company',
            'address' => 'Tasikmalaya',
            'email' => 'pending-company@test.com',
            'status' => 'pending',
        ]);

        $profile = $this->createJobSeeker();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson(
                "/api/company/job-seekers/{$profile->id}"
            );

        $response->assertStatus(403);
    }

    public function test_company_cannot_view_private_profile(): void
    {
        $company = $this->createApprovedCompany();

        $profile = $this->createJobSeeker(false);

        $response = $this
            ->actingAs($company, 'sanctum')
            ->getJson(
                "/api/company/job-seekers/{$profile->id}"
            );

        $response->assertStatus(404);
    }

    public function test_company_cannot_download_cv_of_private_profile(): void
    {
        Storage::fake('local');

        $company = $this->createApprovedCompany();

        $profile = $this->createJobSeeker(false);

        $profile->update([
            'cv' => 'job-seekers/cv/test.pdf',
        ]);

        Storage::disk('local')->put(
            'job-seekers/cv/test.pdf',
            'test cv'
        );

        $response = $this
            ->actingAs($company, 'sanctum')
            ->get(
                "/api/company/job-seekers/{$profile->id}/cv"
            );

        $response->assertStatus(404);
    }

    public function test_non_company_cannot_view_company_job_seeker_endpoint(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $profile = $this->createJobSeeker();

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson(
                "/api/company/job-seekers/{$profile->id}"
            );

        $response->assertStatus(403);
    }
}