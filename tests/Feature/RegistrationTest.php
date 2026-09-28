<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\JobSeekerProfile;
use App\Models\User;
use App\Notifications\AdminPendingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pencari kerja dapat membuat akun sendiri
     * dan langsung memiliki role pencari_kerja.
     */
    public function test_job_seeker_can_register_without_admin_verification(): void
    {
        $response = $this->postJson('/api/register', [
            'role' => 'pencari_kerja',
            'name' => 'Budi Pencari Kerja',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Akun pencari kerja berhasil dibuat.'
            )
            ->assertJsonPath(
                'data.user.role',
                'pencari_kerja'
            )
            ->assertJsonPath(
                'data.profile.is_public',
                false
            );

        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'role' => 'pencari_kerja',
        ]);

        $user = User::where(
            'email',
            'budi@example.com'
        )->firstOrFail();

        $this->assertDatabaseHas('job_seeker_profiles', [
            'user_id' => $user->id,
            'full_name' => 'Budi Pencari Kerja',
            'is_public' => false,
        ]);
    }

    /**
 * Pencari kerja yang baru mendaftar
 * dapat langsung login tanpa verifikasi Admin.
 */
public function test_registered_job_seeker_can_login_immediately(): void
{
    $registerResponse = $this->postJson('/api/register', [
        'role' => 'pencari_kerja',
        'name' => 'Budi Login Test',
        'email' => 'budi-login@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $registerResponse->assertStatus(201);

    $loginResponse = $this->postJson('/api/login', [
        'email' => 'budi-login@example.com',
        'password' => 'password123',
    ]);

    $loginResponse
        ->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath(
            'message',
            'Login berhasil'
        )
        ->assertJsonPath(
            'data.user.email',
            'budi-login@example.com'
        )
        ->assertJsonPath(
            'data.user.role',
            'pencari_kerja'
        );

    $loginResponse->assertJsonStructure([
        'success',
        'message',
        'data' => [
            'user',
            'token',
        ],
    ]);
}

    /**
     * Perusahaan dapat register tetapi
     * tetap harus menunggu verifikasi Admin.
     */
    public function test_company_can_register_and_wait_for_admin_verification(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/register', [
            'role' => 'perusahaan',
            'name' => 'Pemilik PT Maju',
            'email' => 'owner@majujaya.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'PT Maju Jaya',
            'description' => 'Perusahaan teknologi.',
            'address' => 'Tasikmalaya',
            'phone' => '081234567890',
            'company_email' => 'hrd@majujaya.test',
            'website' => 'https://majujaya.test',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Akun perusahaan berhasil dibuat dan menunggu verifikasi Admin.'
            )
            ->assertJsonPath(
                'data.user.role',
                'perusahaan'
            )
            ->assertJsonPath(
                'data.company.status',
                'pending'
            );

        $user = User::where(
            'email',
            'owner@majujaya.test'
        )->firstOrFail();

        $this->assertDatabaseHas('companies', [
            'user_id' => $user->id,
            'name' => 'PT Maju Jaya',
            'status' => 'pending',
        ]);

        Notification::assertSentTo(
            $admin,
            AdminPendingNotification::class
        );
    }

    /**
     * User tidak boleh memilih role admin
     * melalui registrasi publik.
     */
    public function test_public_registration_cannot_create_admin_account(): void
    {
        $response = $this->postJson('/api/register', [
            'role' => 'admin',
            'name' => 'Admin Palsu',
            'email' => 'fakeadmin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'role',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'fakeadmin@example.com',
        ]);
    }

    /**
     * Role selain pencari_kerja/perusahaan ditolak.
     */
    public function test_invalid_registration_role_is_rejected(): void
    {
        $response = $this->postJson('/api/register', [
            'role' => 'petugas',
            'name' => 'User Test',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'role',
        ]);
    }

    /**
     * Email tidak boleh digunakan dua kali.
     */
    public function test_duplicate_email_is_rejected(): void
{
    User::factory()->create([
        'email' => 'same@example.com',
        'role' => 'pencari_kerja',
    ]);

    $response = $this->postJson('/api/register', [
        'role' => 'pencari_kerja',
        'name' => 'User Kedua',
        'email' => 'same@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrors([
        'email',
    ]);
}

    /**
     * Field perusahaan wajib diisi
     * ketika memilih role perusahaan.
     */
    public function test_company_registration_requires_company_fields(): void
    {
        $response = $this->postJson('/api/register', [
            'role' => 'perusahaan',
            'name' => 'Pemilik Perusahaan',
            'email' => 'owner2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'company_name',
            'address',
        ]);
    }
}