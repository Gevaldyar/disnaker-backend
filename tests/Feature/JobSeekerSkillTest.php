<?php

namespace Tests\Feature;

use App\Models\JobSeekerProfile;
use App\Models\JobSeekerSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobSeekerSkillTest extends TestCase
{
    use RefreshDatabase;

    private function createJobSeeker(): array
    {
        $user = User::factory()->create([
            'name' => 'Pencari Kerja Test',
            'email' => 'jobseeker@test.com',
            'password' => 'password123',
            'role' => 'pencari_kerja',
        ]);

        $profile = JobSeekerProfile::create([
            'user_id' => $user->id,
            'full_name' => 'Pencari Kerja Test',
            'headline' => 'Web Developer',
            'bio' => 'Testing profile.',
            'is_public' => true,
        ]);

        return [$user, $profile];
    }

    public function test_job_seeker_can_list_skills(): void
    {
        [$user, $profile] = $this->createJobSeeker();

        JobSeekerSkill::create([
            'job_seeker_profile_id' => $profile->id,
            'skill_name' => 'Laravel',
            'level' => 'Intermediate',
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/job-seeker/skills');

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.0.skill_name',
                'Laravel'
            );
    }

    public function test_job_seeker_can_create_skill(): void
    {
        [$user] = $this->createJobSeeker();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/job-seeker/skills', [
                'skill_name' => 'PHP',
                'level' => 'Intermediate',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath(
                'data.skill_name',
                'PHP'
            );

        $this->assertDatabaseHas(
            'job_seeker_skills',
            [
                'skill_name' => 'PHP',
            ]
        );
    }

    public function test_job_seeker_cannot_create_duplicate_skill(): void
    {
        [$user, $profile] = $this->createJobSeeker();

        JobSeekerSkill::create([
            'job_seeker_profile_id' => $profile->id,
            'skill_name' => 'Laravel',
            'level' => 'Intermediate',
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/job-seeker/skills', [
                'skill_name' => 'Laravel',
                'level' => 'Advanced',
            ]);

        $response->assertStatus(422);
    }

    public function test_job_seeker_can_update_own_skill(): void
    {
        [$user, $profile] = $this->createJobSeeker();

        $skill = JobSeekerSkill::create([
            'job_seeker_profile_id' => $profile->id,
            'skill_name' => 'PHP',
            'level' => 'Beginner',
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->putJson(
                "/api/job-seeker/skills/{$skill->id}",
                [
                    'skill_name' => 'PHP',
                    'level' => 'Advanced',
                ]
            );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.level',
                'Advanced'
            );
    }

    public function test_job_seeker_can_delete_own_skill(): void
    {
        [$user, $profile] = $this->createJobSeeker();

        $skill = JobSeekerSkill::create([
            'job_seeker_profile_id' => $profile->id,
            'skill_name' => 'MySQL',
            'level' => 'Intermediate',
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->deleteJson(
                "/api/job-seeker/skills/{$skill->id}"
            );

        $response->assertStatus(200);

        $this->assertDatabaseMissing(
            'job_seeker_skills',
            [
                'id' => $skill->id,
            ]
        );
    }

    public function test_company_cannot_access_job_seeker_skills(): void
    {
        $company = User::factory()->create([
            'email' => 'company@test.com',
            'password' => 'password123',
            'role' => 'perusahaan',
        ]);

        $response = $this
            ->actingAs($company, 'sanctum')
            ->getJson('/api/job-seeker/skills');

        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_job_seeker_skills_endpoint(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/job-seeker/skills');

        $response->assertStatus(403);
    }
}