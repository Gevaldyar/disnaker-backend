<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobFlowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_company_to_public_job_flow(): void
    {
        Storage::fake('public');

        /*
        |--------------------------------------------------------------------------
        | 1. Buat Admin
        |--------------------------------------------------------------------------
        */

        $admin = User::factory()->create([
            'name' => 'Admin Disnaker',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. Perusahaan register
        |--------------------------------------------------------------------------
        */

        $registerResponse = $this->postJson(
            '/api/company/register',
            [
                'name' => 'PT Integrasi Test',
                'email' => 'company-integration@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',

                'company_name' => 'PT Integrasi Test',
                'address' => 'Tasikmalaya',
                'description' => 'Perusahaan untuk integration test.',
                'phone' => '081234567890',
                'company_email' => 'info@integrasitest.com',
                'website' => 'https://integrasitest.com',
            ]
        );

        $registerResponse->assertStatus(201);

        $companyUser = User::where(
            'email',
            'company-integration@test.com'
        )->firstOrFail();

        $company = Company::where(
            'user_id',
            $companyUser->id
        )->firstOrFail();

        $this->assertEquals(
            'perusahaan',
            $companyUser->role
        );

        $this->assertEquals(
            'pending',
            $company->status
        );

        /*
        |--------------------------------------------------------------------------
        | 3. Perusahaan login
        |--------------------------------------------------------------------------
        */

        $companyLogin = $this->postJson(
            '/api/login',
            [
                'email' => 'company-integration@test.com',
                'password' => 'password123',
            ]
        );

        $companyLogin->assertStatus(200);

        /*
        |--------------------------------------------------------------------------
        | 4. Perusahaan pending tidak boleh membuat lowongan
        |--------------------------------------------------------------------------
        */

        Sanctum::actingAs($companyUser);

        $pendingJobResponse = $this->post(
            '/api/company/jobs',
            [
                'title' => 'Staff Administrasi',
                'poster' => UploadedFile::fake()->image(
                    'poster.jpg'
                ),
                'location' => 'Tasikmalaya',
                'description' => 'Deskripsi lowongan.',
                'expires_at' => now()
                    ->addDays(30)
                    ->format('Y-m-d'),
            ],
            [
                'Accept' => 'application/json',
            ]
        );

        $pendingJobResponse->assertStatus(403);

        /*
        |--------------------------------------------------------------------------
        | 5. Admin login
        |--------------------------------------------------------------------------
        */

        Sanctum::actingAs($admin);

        $adminLogin = $this->postJson(
            '/api/login',
            [
                'email' => 'admin@test.com',
                'password' => 'password123',
            ]
        );

        $adminLogin->assertStatus(200);

        /*
        |--------------------------------------------------------------------------
        | 6. Admin approve perusahaan
        |--------------------------------------------------------------------------
        */

        $approveCompanyResponse = $this->patchJson(
            "/api/admin/companies/{$company->id}/approve"
        );

        $approveCompanyResponse->assertStatus(200);

        /*
        |--------------------------------------------------------------------------
        | 7. Pastikan data perusahaan benar-benar sudah approved
        |--------------------------------------------------------------------------
        */

        $company->refresh();

        $this->assertEquals(
            'approved',
            $company->status
        );

        $this->assertNotNull(
            $company->verified_at
        );

        /*
        |--------------------------------------------------------------------------
        | 8. REFRESH USER
        |--------------------------------------------------------------------------
        |
        | Penting:
        | Sebelumnya $companyUser sudah dipakai ketika company masih pending.
        | Relasi company bisa tersimpan di memory dengan status pending.
        | Kita ambil ulang user dari database agar relasi company terbaru.
        |--------------------------------------------------------------------------
        */

        $companyUser = User::with('company')
            ->where('id', $companyUser->id)
            ->firstOrFail();

        $companyUser->load('company');

        $this->assertEquals(
            'perusahaan',
            $companyUser->role
        );

        $this->assertNotNull(
            $companyUser->company
        );

        $this->assertEquals(
            'approved',
            $companyUser->company->status
        );

        /*
        |--------------------------------------------------------------------------
        | 9. Perusahaan membuat lowongan
        |--------------------------------------------------------------------------
        */

        Sanctum::actingAs($companyUser);

        $createJobResponse = $this->post(
            '/api/company/jobs',
            [
                'title' => 'Staff Administrasi Integrasi',
                'poster' => UploadedFile::fake()->image(
                    'poster.jpg'
                ),
                'location' => 'Tasikmalaya',
                'description' => 'Kirim CV ke hrd@integrasitest.com',
                'expires_at' => now()
                    ->addDays(30)
                    ->format('Y-m-d'),
            ],
            [
                'Accept' => 'application/json',
            ]
        );

        $createJobResponse->assertStatus(201);

        $job = Job::where(
            'company_id',
            $company->id
        )->firstOrFail();

        $this->assertEquals(
            'draft',
            $job->status
        );

        /*
        |--------------------------------------------------------------------------
        | 10. Perusahaan submit lowongan
        |--------------------------------------------------------------------------
        */

        $submitResponse = $this->postJson(
            "/api/company/jobs/{$job->id}/submit"
        );

        $submitResponse->assertStatus(200);

        $job->refresh();

        $this->assertEquals(
            'pending',
            $job->status
        );

        /*
        |--------------------------------------------------------------------------
        | 11. Lowongan pending belum muncul di public API
        |--------------------------------------------------------------------------
        */

        $beforeApproval = $this->getJson(
            '/api/jobs'
        );

        $beforeApproval
            ->assertStatus(200)
            ->assertJsonMissing([
                'id' => $job->id,
            ]);

        /*
        |--------------------------------------------------------------------------
        | 12. Admin approve lowongan
        |--------------------------------------------------------------------------
        */

        Sanctum::actingAs($admin);

        $approveJobResponse = $this->patchJson(
            "/api/admin/jobs/{$job->id}/approve"
        );

        $approveJobResponse->assertStatus(200);

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

        /*
        |--------------------------------------------------------------------------
        | 13. Lowongan muncul di public API
        |--------------------------------------------------------------------------
        */

        $afterApproval = $this->getJson(
            '/api/jobs'
        );

        $afterApproval
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $job->id,
                'title' => 'Staff Administrasi Integrasi',
            ]);

        /*
        |--------------------------------------------------------------------------
        | 14. Detail lowongan public
        |--------------------------------------------------------------------------
        */

        $detailResponse = $this->getJson(
            "/api/jobs/{$job->id}"
        );

        $detailResponse
            ->assertStatus(200)
            ->assertJsonPath(
                'data.id',
                $job->id
            )
            ->assertJsonPath(
                'data.title',
                'Staff Administrasi Integrasi'
            )
            ->assertJsonPath(
                'data.company.name',
                'PT Integrasi Test'
            );

        /*
        |--------------------------------------------------------------------------
        | 15. Views bertambah
        |--------------------------------------------------------------------------
        */

        $job->refresh();

        $this->assertEquals(
            1,
            $job->views
        );
    }
}