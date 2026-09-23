<?php

namespace Tests\Feature;

use App\Models\JobSeekerEducation;
use App\Models\JobSeekerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobSeekerEducationTest extends TestCase
{
    use RefreshDatabase;

    private function createJobSeeker(
    string $email = 'education@test.com'
): array
{
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

    public function test_job_seeker_can_list_educations(): void
    {
        [$user, $profile] = $this->createJobSeeker();

        JobSeekerEducation::create([
            'job_seeker_profile_id' => $profile->id,
            'institution' => 'Universitas Test',
            'degree' => 'S1',
            'field_of_study' => 'Sistem Informasi',
            'start_date' => '2022-09-01',
            'end_date' => '2026-07-30',
            'is_current' => false,
            'description' => null,
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/job-seeker/educations');

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.0.institution',
                'Universitas Test'
            );
    }

    public function test_job_seeker_can_create_education(): void
    {
        [$user] = $this->createJobSeeker();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/job-seeker/educations', [
                'institution' => 'Universitas Test',
                'degree' => 'S1',
                'field_of_study' => 'Sistem Informasi',
                'start_date' => '2022-09-01',
                'end_date' => '2026-07-30',
                'is_current' => false,
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath(
                'data.institution',
                'Universitas Test'
            );

        $this->assertDatabaseHas(
            'job_seeker_educations',
            [
                'institution' => 'Universitas Test',
                'degree' => 'S1',
            ]
        );
    }

    public function test_current_education_has_no_end_date(): void
    {
        [$user] = $this->createJobSeeker();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/job-seeker/educations', [
                'institution' => 'Universitas Test',
                'degree' => 'S1',
                'field_of_study' => 'Sistem Informasi',
                'start_date' => '2025-09-01',
                'is_current' => true,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas(
            'job_seeker_educations',
            [
                'institution' => 'Universitas Test',
                'is_current' => true,
                'end_date' => null,
            ]
        );
    }

    public function test_invalid_education_dates_are_rejected(): void
    {
        [$user] = $this->createJobSeeker();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/job-seeker/educations', [
                'institution' => 'Universitas Test',
                'start_date' => '2026-01-01',
                'end_date' => '2025-01-01',
            ]);

        $response->assertStatus(422);
    }

    public function test_job_seeker_can_update_own_education(): void
    {
        [$user, $profile] = $this->createJobSeeker();

        $education = JobSeekerEducation::create([
            'job_seeker_profile_id' => $profile->id,
            'institution' => 'Universitas Lama',
            'degree' => 'D3',
            'field_of_study' => 'Manajemen',
            'start_date' => '2020-01-01',
            'end_date' => '2023-01-01',
            'is_current' => false,
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->putJson(
                "/api/job-seeker/educations/{$education->id}",
                [
                    'institution' => 'Universitas Baru',
                    'degree' => 'S1',
                    'field_of_study' => 'Sistem Informasi',
                    'start_date' => '2023-01-01',
                    'end_date' => '2027-01-01',
                    'is_current' => false,
                ]
            );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.institution',
                'Universitas Baru'
            );
    }

    public function test_job_seeker_can_delete_own_education(): void
    {
        [$user, $profile] = $this->createJobSeeker();

        $education = JobSeekerEducation::create([
            'job_seeker_profile_id' => $profile->id,
            'institution' => 'Universitas Test',
            'degree' => 'S1',
            'field_of_study' => 'Sistem Informasi',
            'is_current' => false,
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->deleteJson(
                "/api/job-seeker/educations/{$education->id}"
            );

        $response->assertStatus(200);

        $this->assertDatabaseMissing(
            'job_seeker_educations',
            [
                'id' => $education->id,
            ]
        );
    }

    public function test_job_seeker_cannot_manage_another_users_education(): void
    {
        [$userA] = $this->createJobSeeker(
    'education-a@test.com'
);

        [$userB, $profileB] = $this->createJobSeeker(
    'education-b@test.com'
);

        $education = JobSeekerEducation::create([
            'job_seeker_profile_id' => $profileB->id,
            'institution' => 'Universitas B',
            'degree' => 'S1',
            'field_of_study' => 'Sistem Informasi',
            'is_current' => false,
        ]);

        $response = $this
            ->actingAs($userA, 'sanctum')
            ->deleteJson(
                "/api/job-seeker/educations/{$education->id}"
            );

        $response->assertStatus(404);

        $this->assertDatabaseHas(
            'job_seeker_educations',
            [
                'id' => $education->id,
            ]
        );
    }
}