<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use App\Notifications\AdminPendingNotification;
use App\Notifications\JobStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat Admin untuk testing.
     */
    private function createAdmin(
        string $email = 'admin@test.com'
    ): User {
        return User::factory()->create([
            'name' => 'Admin Test',
            'email' => $email,
            'password' => 'password123',
            'role' => 'admin',
        ]);
    }

    /**
     * Membuat user perusahaan + profil perusahaan.
     */
    private function createCompany(
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
            'description' => 'Perusahaan untuk testing notification.',
            'address' => 'Tasikmalaya',
            'phone' => '081234567890',
            'email' => $email,
            'website' => 'https://example.com',
            'logo' => null,
            'status' => 'approved',
            'rejection_reason' => null,
            'verified_at' => now(),
        ]);

        return [$user, $company];
    }

    /**
     * Membuat lowongan untuk testing.
     */
    private function createJob(
        Company $company,
        string $status = 'draft'
    ): Job {
        return Job::create([
            'company_id' => $company->id,
            'title' => 'Staff Administrasi Test',
            'poster' => null,
            'location' => 'Tasikmalaya',
            'description' => 'Deskripsi lowongan untuk testing notification.',
            'published_at' => $status === 'approved'
                ? now()
                : null,
            'expires_at' => now()->addDays(30),
            'views' => 0,
            'status' => $status,
            'rejection_reason' => null,
            'verified_at' => $status === 'approved'
                ? now()
                : null,
        ]);
    }

    /**
     * Admin mendapat notification ketika perusahaan mendaftar.
     */
    public function test_admin_receives_notification_when_company_registers(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();

        $response = $this->postJson('/api/company/register', [
            'name' => 'Pemilik PT Notification',
            'email' => 'register-notification@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',

            'company_name' => 'PT Notification Test',
            'address' => 'Tasikmalaya',
            'description' => 'Testing notification perusahaan baru.',
            'phone' => '081234567890',
            'company_email' => 'info@notification.test',
            'website' => 'https://notification.test',
        ]);

        $response->assertStatus(201);

        Notification::assertSentTo(
            $admin,
            AdminPendingNotification::class,
            function (AdminPendingNotification $notification) {
                return $notification->type === 'company_registered'
                    && $notification->companyId !== null
                    && $notification->jobId === null;
            }
        );
    }

    /**
     * Admin mendapat notification ketika perusahaan submit lowongan.
     */
    public function test_admin_receives_notification_when_job_is_submitted(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        [$companyUser, $company] = $this->createCompany(
            'company-submit@test.com',
            'PT Submit Notification'
        );

        $job = $this->createJob($company, 'draft');

        Sanctum::actingAs($companyUser);

        $response = $this->postJson(
            "/api/company/jobs/{$job->id}/submit"
        );

        $response->assertStatus(200);

        $job->refresh();

        $this->assertEquals(
            'pending',
            $job->status
        );

        Notification::assertSentTo(
            $admin,
            AdminPendingNotification::class,
            function (AdminPendingNotification $notification) use ($job, $company) {
                return $notification->type === 'job_submitted'
                    && $notification->companyId === $company->id
                    && $notification->jobId === $job->id;
            }
        );
    }

    /**
     * Perusahaan mendapat notification ketika Admin approve lowongan.
     */
    public function test_company_receives_notification_when_job_is_approved(): void
    {
        Notification::fake();

        $admin = $this->createAdmin(
            'admin-approve@test.com'
        );

        [$companyUser, $company] = $this->createCompany(
            'company-approve@test.com',
            'PT Approve Notification'
        );

        $job = $this->createJob($company, 'pending');

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/jobs/{$job->id}/approve"
        );

        $response->assertStatus(200);

        $job->refresh();

        $this->assertEquals(
            'approved',
            $job->status
        );

        Notification::assertSentTo(
            $companyUser,
            JobStatusNotification::class,
            function (JobStatusNotification $notification) use ($job) {
                return $notification->status === 'approved'
                    && $notification->job->id === $job->id;
            }
        );
    }

    /**
     * Perusahaan mendapat notification ketika Admin reject lowongan.
     */
    public function test_company_receives_notification_when_job_is_rejected(): void
    {
        Notification::fake();

        $admin = $this->createAdmin(
            'admin-reject@test.com'
        );

        [$companyUser, $company] = $this->createCompany(
            'company-reject@test.com',
            'PT Reject Notification'
        );

        $job = $this->createJob($company, 'pending');

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/jobs/{$job->id}/reject",
            [
                'rejection_reason' => 'Informasi lowongan belum lengkap.',
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

        Notification::assertSentTo(
            $companyUser,
            JobStatusNotification::class,
            function (JobStatusNotification $notification) use ($job) {
                return $notification->status === 'rejected'
                    && $notification->job->id === $job->id;
            }
        );
    }

    /**
     * Perusahaan dapat melihat notification dan unread count.
     */
    public function test_company_can_view_notifications(): void
    {
        [$companyUser, $company] = $this->createCompany(
            'company-list@test.com',
            'PT Notification List'
        );

        $job = $this->createJob($company, 'approved');

        $companyUser->notify(
            new JobStatusNotification($job, 'approved')
        );

        Sanctum::actingAs($companyUser);

        $response = $this->getJson(
            '/api/company/notifications'
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'success',
                true
            )
            ->assertJsonPath(
                'unread_count',
                1
            );
    }

    /**
     * Perusahaan dapat menandai notification sebagai dibaca.
     */
    public function test_company_can_mark_notification_as_read(): void
    {
        [$companyUser, $company] = $this->createCompany(
            'company-read@test.com',
            'PT Notification Read'
        );

        $job = $this->createJob($company, 'approved');

        $companyUser->notify(
            new JobStatusNotification($job, 'approved')
        );

        $notification = $companyUser
            ->notifications()
            ->latest()
            ->firstOrFail();

        Sanctum::actingAs($companyUser);

        $response = $this->patchJson(
            "/api/company/notifications/{$notification->id}/read"
        );

        $response->assertStatus(200);

        $notification->refresh();

        $this->assertNotNull(
            $notification->read_at
        );
    }

    /**
     * Perusahaan dapat menandai semua notification sebagai dibaca.
     */
    public function test_company_can_mark_all_notifications_as_read(): void
    {
        [$companyUser, $company] = $this->createCompany(
            'company-read-all@test.com',
            'PT Notification Read All'
        );

        $job = $this->createJob($company, 'approved');

        $companyUser->notify(
            new JobStatusNotification($job, 'approved')
        );

        $companyUser->notify(
            new JobStatusNotification($job, 'rejected')
        );

        Sanctum::actingAs($companyUser);

        $response = $this->patchJson(
            '/api/company/notifications/read-all'
        );

        $response->assertStatus(200);

        $this->assertEquals(
            0,
            $companyUser
                ->fresh()
                ->unreadNotifications()
                ->count()
        );
    }
}