<?php

namespace Tests\Feature;

use App\Models\JobSeekerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobSeekerProfileFileTest extends TestCase
{
    use RefreshDatabase;

    private function createJobSeeker(): User
    {
        $user = User::factory()->create([
            'name' => 'Pencari Kerja Test',
            'email' => 'profile-file@test.com',
            'password' => 'password123',
            'role' => 'pencari_kerja',
        ]);

        JobSeekerProfile::create([
            'user_id' => $user->id,
            'full_name' => 'Pencari Kerja Test',
            'is_public' => true,
        ]);

        return $user;
    }

    public function test_job_seeker_can_upload_cv(): void
    {
        Storage::fake('public');

        $user = $this->createJobSeeker();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->put(
                '/api/job-seeker/profile',
                [
                    'full_name' => 'Pencari Kerja Test',
                    'is_public' => true,
                    'cv' => UploadedFile::fake()->create(
                        'cv.pdf',
                        100,
                        'application/pdf'
                    ),
                ],
                [
                    'Accept' => 'application/json',
                ]
            );

        $response->assertStatus(200);

        $user->load('jobSeekerProfile');

        $profile = $user->jobSeekerProfile;

        $this->assertNotNull($profile->cv);

        Storage::disk('public')->assertExists(
            $profile->cv
        );
    }

    public function test_invalid_cv_format_is_rejected(): void
    {
        $user = $this->createJobSeeker();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->put(
                '/api/job-seeker/profile',
                [
                    'full_name' => 'Pencari Kerja Test',
                    'cv' => UploadedFile::fake()->create(
                        'virus.exe',
                        100,
                        'application/octet-stream'
                    ),
                ],
                [
                    'Accept' => 'application/json',
                ]
            );

        $response->assertStatus(422);
    }

    public function test_job_seeker_can_change_public_visibility(): void
    {
        $user = $this->createJobSeeker();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->putJson(
                '/api/job-seeker/profile',
                [
                    'full_name' => 'Pencari Kerja Test',
                    'is_public' => false,
                ]
            );

        $response->assertStatus(200);

        $user->load('jobSeekerProfile');

        $this->assertFalse(
            $user->jobSeekerProfile->is_public
        );
    }

    public function test_company_cannot_update_job_seeker_profile(): void
    {
        $company = User::factory()->create([
            'name' => 'Company Test',
            'email' => 'company-profile@test.com',
            'password' => 'password123',
            'role' => 'perusahaan',
        ]);

        $response = $this
            ->actingAs($company, 'sanctum')
            ->putJson(
                '/api/job-seeker/profile',
                [
                    'full_name' => 'Tidak Boleh',
                ]
            );

        $response->assertStatus(403);
    }
}