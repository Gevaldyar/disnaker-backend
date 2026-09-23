<?php

namespace Tests\Feature;

use App\Models\JobSeekerExperience;
use App\Models\JobSeekerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobSeekerExperienceTest extends TestCase
{
    use RefreshDatabase;

    private function createJobSeeker(
        string $email
    ): array {
        $user = User::factory()->create([
            'name' => 'Pencari Kerja Test',
            'email' => $email,
            'password' => 'password123',
            'role' => 'pencari_kerja',
        ]);

        $profile = JobSeekerProfile::create([
            'user_id' => $user->id,
            'full_name' => 'Pencari Kerja Test',
            'is_public' => true,
        ]);

        return [$user, $profile];
    }

    public function test_job_seeker_can_create_experience(): void
    {
        [$user] = $this->createJobSeeker(
            'experience-create@test.com'
        );

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/job-seeker/experiences', [
                'company_name' => 'PT Test',
                'position' => 'Backend Developer',
                'employment_type' => 'Full-time',
                'location' => 'Tasikmalaya',
                'start_date' => '2025-01-01',
                'end_date' => '2026-08-31',
                'is_current' => false,
                'description' => 'Testing.',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath(
                'data.company_name',
                'PT Test'
            );

        $this->assertDatabaseHas(
            'job_seeker_experiences',
            [
                'company_name' => 'PT Test',
                'position' => 'Backend Developer',
            ]
        );
    }

    public function test_current_experience_has_no_end_date(): void
    {
        [$user] = $this->createJobSeeker(
            'experience-current@test.com'
        );

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/job-seeker/experiences', [
                'company_name' => 'Freelance',
                'position' => 'Web Developer',
                'start_date' => '2026-01-01',
                'is_current' => true,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas(
            'job_seeker_experiences',
            [
                'company_name' => 'Freelance',
                'is_current' => true,
                'end_date' => null,
            ]
        );
    }

    public function test_invalid_experience_dates_are_rejected(): void
    {
        [$user] = $this->createJobSeeker(
            'experience-date@test.com'
        );

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/job-seeker/experiences', [
                'company_name' => 'PT Test',
                'position' => 'Developer',
                'start_date' => '2026-01-01',
                'end_date' => '2025-01-01',
            ]);

        $response->assertStatus(422);
    }

    public function test_job_seeker_can_update_own_experience(): void
    {
        [$user, $profile] = $this->createJobSeeker(
            'experience-update@test.com'
        );

        $experience = JobSeekerExperience::create([
            'job_seeker_profile_id' => $profile->id,
            'company_name' => 'PT Lama',
            'position' => 'Developer',
            'start_date' => '2024-01-01',
            'end_date' => '2025-01-01',
            'is_current' => false,
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->putJson(
                "/api/job-seeker/experiences/{$experience->id}",
                [
                    'company_name' => 'PT Baru',
                    'position' => 'Backend Developer',
                    'start_date' => '2025-01-01',
                    'end_date' => '2026-01-01',
                    'is_current' => false,
                ]
            );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.company_name',
                'PT Baru'
            );
    }

    public function test_job_seeker_can_delete_own_experience(): void
    {
        [$user, $profile] = $this->createJobSeeker(
            'experience-delete@test.com'
        );

        $experience = JobSeekerExperience::create([
            'job_seeker_profile_id' => $profile->id,
            'company_name' => 'PT Test',
            'position' => 'Developer',
            'is_current' => false,
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->deleteJson(
                "/api/job-seeker/experiences/{$experience->id}"
            );

        $response->assertStatus(200);

        $this->assertDatabaseMissing(
            'job_seeker_experiences',
            [
                'id' => $experience->id,
            ]
        );
    }

    public function test_job_seeker_cannot_manage_another_users_experience(): void
    {
        [$userA] = $this->createJobSeeker(
            'experience-a@test.com'
        );

        [, $profileB] = $this->createJobSeeker(
            'experience-b@test.com'
        );

        $experience = JobSeekerExperience::create([
            'job_seeker_profile_id' => $profileB->id,
            'company_name' => 'PT User B',
            'position' => 'Developer',
            'is_current' => false,
        ]);

        $response = $this
            ->actingAs($userA, 'sanctum')
            ->deleteJson(
                "/api/job-seeker/experiences/{$experience->id}"
            );

        $response->assertStatus(404);

        $this->assertDatabaseHas(
            'job_seeker_experiences',
            [
                'id' => $experience->id,
            ]
        );
    }

    public function test_company_cannot_access_job_seeker_experiences(): void
    {
        $company = User::factory()->create([
            'name' => 'Company Test',
            'email' => 'experience-company@test.com',
            'password' => 'password123',
            'role' => 'perusahaan',
        ]);

        $response = $this
            ->actingAs($company, 'sanctum')
            ->getJson('/api/job-seeker/experiences');

        $response->assertStatus(403);
    }
}