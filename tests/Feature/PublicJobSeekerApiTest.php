<?php

namespace Tests\Feature;

use App\Models\JobSeekerExperience;
use App\Models\JobSeekerProfile;
use App\Models\JobSeekerSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicJobSeekerApiTest extends TestCase
{
    use RefreshDatabase;

    private function createProfile(
        string $email,
        bool $isPublic = true
    ): JobSeekerProfile {
        $user = User::factory()->create([
            'name' => 'Job Seeker Test',
            'email' => $email,
            'password' => 'password123',
            'role' => 'pencari_kerja',
        ]);

        return JobSeekerProfile::create([
            'user_id' => $user->id,
            'full_name' => 'Budi Santoso',
            'headline' => 'Laravel Backend Developer',
            'bio' => 'Berpengalaman di bidang web development.',
            'city' => 'Tasikmalaya',
            'gender' => 'Laki-laki',
            'phone' => '081234567890',
            'address' => 'Alamat Test',
            'birth_date' => '2002-01-01',
            'is_public' => $isPublic,
        ]);
    }

    public function test_public_api_only_shows_public_profiles(): void
    {
        $publicProfile = $this->createProfile(
            'public@test.com',
            true
        );

        $this->createProfile(
            'private@test.com',
            false
        );

        $response = $this->getJson(
            '/api/job-seekers'
        );

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.id',
            $publicProfile->id
        );
    }

    public function test_public_profile_detail_can_be_viewed(): void
    {
        $profile = $this->createProfile(
            'detail@test.com',
            true
        );

        $response = $this->getJson(
            "/api/job-seekers/{$profile->id}"
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.id',
                $profile->id
            )
            ->assertJsonPath(
                'data.full_name',
                'Budi Santoso'
            );
    }

    public function test_private_profile_detail_returns_404(): void
    {
        $profile = $this->createProfile(
            'private-detail@test.com',
            false
        );

        $response = $this->getJson(
            "/api/job-seekers/{$profile->id}"
        );

        $response->assertStatus(404);
    }

    public function test_public_profile_does_not_expose_sensitive_fields(): void
    {
        $profile = $this->createProfile(
            'privacy@test.com',
            true
        );

        $response = $this->getJson(
            "/api/job-seekers/{$profile->id}"
        );

        $response
            ->assertStatus(200)
            ->assertJsonMissingPath('data.phone')
            ->assertJsonMissingPath('data.address')
            ->assertJsonMissingPath('data.birth_date')
            ->assertJsonMissingPath('data.cv')
            ->assertJsonMissingPath('data.user_id');
    }

    public function test_public_api_can_search_profiles(): void
    {
        $profile = $this->createProfile(
            'search@test.com',
            true
        );

        $response = $this->getJson(
            '/api/job-seekers?search=Laravel'
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.0.id',
                $profile->id
            );
    }

    public function test_public_api_can_filter_by_city(): void
    {
        $profile = $this->createProfile(
            'city@test.com',
            true
        );

        $response = $this->getJson(
            '/api/job-seekers?city=Tasikmalaya'
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.0.id',
                $profile->id
            );
    }

    public function test_public_api_can_filter_by_skill(): void
    {
        $profile = $this->createProfile(
            'skill@test.com',
            true
        );

        JobSeekerSkill::create([
            'job_seeker_profile_id' => $profile->id,
            'skill_name' => 'Laravel',
            'level' => 'Intermediate',
        ]);

        $response = $this->getJson(
            '/api/job-seekers?skill=Laravel'
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.0.id',
                $profile->id
            );
    }

    public function test_public_profile_includes_skills_and_experiences(): void
    {
        $profile = $this->createProfile(
            'complete@test.com',
            true
        );

        JobSeekerSkill::create([
            'job_seeker_profile_id' => $profile->id,
            'skill_name' => 'PHP',
            'level' => 'Advanced',
        ]);

        JobSeekerExperience::create([
            'job_seeker_profile_id' => $profile->id,
            'company_name' => 'PT Test',
            'position' => 'Backend Developer',
            'employment_type' => 'Full-time',
            'location' => 'Tasikmalaya',
            'start_date' => '2024-01-01',
            'end_date' => '2025-01-01',
            'is_current' => false,
            'description' => 'Mengembangkan API.',
        ]);

        $response = $this->getJson(
            "/api/job-seekers/{$profile->id}"
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.skills.0.skill_name',
                'PHP'
            )
            ->assertJsonPath(
                'data.experiences.0.company_name',
                'PT Test'
            );
    }
}