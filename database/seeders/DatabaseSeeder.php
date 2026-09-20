<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\JobSeeker;
use App\Models\Page;
use App\Models\Service;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Disnaker',
            'email' => 'admin@disnaker.test',
            'password' => 'password',
            'role' => 'admin',
        ]);
        // Membuat akun perusahaan.
        $user = User::create([
            'name' => 'PT Maju Jaya',
            'email' => 'company@example.com',
            'password' => 'password',
            'role' => 'perusahaan',
        ]);

        // Membuat profil perusahaan.
        $company = Company::create([
            'user_id' => $user->id,
            'name' => 'PT Maju Jaya',
            'description' => 'Perusahaan yang bergerak di bidang teknologi dan layanan bisnis.',
            'address' => 'Jl. HZ Mustofa, Tasikmalaya',
            'phone' => '081234567890',
            'email' => 'hrd@majubersama.com',
            'website' => 'https://example.com',
            'logo' => null,
            'status' => 'approved',
            'rejection_reason' => null,
            'verified_at' => now(),
        ]);

        // Lowongan 1: Approved dan masih aktif.
        Job::create([
            'company_id' => $company->id,
            'title' => 'Staff Administrasi',
            'poster' => null,
            'location' => 'Tasikmalaya',
            'description' => 'Membantu proses administrasi perusahaan.

Persyaratan:
- Pendidikan minimal SMA/SMK
- Menguasai Microsoft Office
- Teliti dan bertanggung jawab

Cara melamar:
Kirim CV dan surat lamaran melalui email hrd@majubersama.com dengan subject "Lamaran Staff Administrasi".',
            'published_at' => now(),
            'expires_at' => today()->addDays(30),
            'views' => 0,
            'status' => 'approved',
        ]);

        // Lowongan 2: Pending, seharusnya tidak muncul di API publik.
        Job::create([
            'company_id' => $company->id,
            'title' => 'Digital Marketing',
            'poster' => null,
            'location' => 'Tasikmalaya',
            'description' => 'Lowongan Digital Marketing untuk kebutuhan perusahaan.',
            'published_at' => null,
            'expires_at' => today()->addDays(30),
            'views' => 0,
            'status' => 'pending',
        ]);

        // Lowongan 3: Approved tetapi sudah expired.
        Job::create([
            'company_id' => $company->id,
            'title' => 'Customer Service',
            'poster' => null,
            'location' => 'Tasikmalaya',
            'description' => 'Lowongan Customer Service yang sudah melewati masa berlaku.',
            'published_at' => today()->subDays(30),
            'expires_at' => today()->subDay(),
            'views' => 0,
            'status' => 'approved',
        ]);

        Page::create([
            'title' => 'Sejarah',
            'slug' => 'sejarah',
            'content' => 'Sejarah Dinas Tenaga Kerja Kota Tasikmalaya.',
            'status' => 'published',
        ]);

        Page::create([
            'title' => 'Struktur Organisasi',
            'slug' => 'struktur-organisasi',
            'content' => 'Struktur organisasi Dinas Tenaga Kerja Kota Tasikmalaya.',
            'status' => 'published',
        ]);

        Page::create([
            'title' => 'Visi & Misi',
            'slug' => 'visi-misi',
            'content' => 'Visi dan Misi Dinas Tenaga Kerja Kota Tasikmalaya.',
            'status' => 'published',
        ]);

        Page::create([
            'title' => 'Tupoksi',
            'slug' => 'tupoksi',
            'content' => 'Tugas pokok dan fungsi Dinas Tenaga Kerja Kota Tasikmalaya.',
            'status' => 'published',
        ]);

Page::create([
    'title' => 'Pejabat Struktural',
    'slug' => 'pejabat-struktural',
    'content' => 'Informasi pejabat struktural Dinas Tenaga Kerja Kota Tasikmalaya.',
    'status' => 'published',
]);
        JobSeeker::factory()->count(10)->create();
        Service::factory()->count(3)->create();
    }
}