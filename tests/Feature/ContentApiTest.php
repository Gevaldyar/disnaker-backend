<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\News;
use App\Models\Page;
use App\Models\Service;
use App\Models\Training;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentApiTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | NEWS
    |--------------------------------------------------------------------------
    */

    public function test_public_news_only_shows_published_news(): void
    {
        News::create([
            'title' => 'Berita Published',
            'summary' => 'Berita yang dapat dilihat publik.',
            'thumbnail' => null,
            'instagram_url' => 'https://instagram.com/disnaker',
            'published_at' => now(),
            'status' => 'published',
        ]);

        News::create([
            'title' => 'Berita Draft',
            'summary' => 'Berita yang belum diterbitkan.',
            'thumbnail' => null,
            'instagram_url' => 'https://www.instagram.com/disnaker',
            'published_at' => null,
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/news');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.title',
            'Berita Published'
        );
    }

    public function test_public_news_detail_can_show_published_news(): void
    {
        $news = News::create([
            'title' => 'Berita Detail',
            'summary' => 'Ringkasan berita.',
            'thumbnail' => null,
            'instagram_url' => 'https://instagram.com/disnaker',
            'published_at' => now(),
            'status' => 'published',
        ]);

        $response = $this->getJson(
            "/api/news/{$news->id}"
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.id',
                $news->id
            )
            ->assertJsonPath(
                'data.title',
                'Berita Detail'
            );
    }

    public function test_public_news_detail_cannot_show_draft_news(): void
    {
        $news = News::create([
            'title' => 'Berita Draft',
            'summary' => 'Berita draft.',
            'thumbnail' => null,
            'instagram_url' => 'https://www.instagram.com/disnaker',
            'published_at' => null,
            'status' => 'draft',
        ]);

        $response = $this->getJson(
            "/api/news/{$news->id}"
        );

        $response->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | ANNOUNCEMENTS
    |--------------------------------------------------------------------------
    */

    public function test_public_announcements_only_shows_published_announcements(): void
    {
        Announcement::create([
            'title' => 'Pengumuman Published',
            'content' => 'Isi pengumuman.',
            'thumbnail' => null,
            'link' => 'https://example.com',
            'published_at' => now(),
            'status' => 'published',
        ]);

        Announcement::create([
            'title' => 'Pengumuman Draft',
            'content' => 'Draft pengumuman.',
            'thumbnail' => null,
            'link' => null,
            'published_at' => null,
            'status' => 'draft',
        ]);

        $response = $this->getJson(
            '/api/announcements'
        );

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.title',
            'Pengumuman Published'
        );
    }

    public function test_public_announcement_detail_cannot_show_draft(): void
    {
        $announcement = Announcement::create([
            'title' => 'Pengumuman Draft',
            'content' => 'Isi draft.',
            'thumbnail' => null,
            'link' => null,
            'published_at' => null,
            'status' => 'draft',
        ]);

        $response = $this->getJson(
            "/api/announcements/{$announcement->id}"
        );

        $response->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | TRAININGS
    |--------------------------------------------------------------------------
    */

    public function test_public_trainings_only_shows_published_trainings(): void
    {
        Training::create([
            'title' => 'Pelatihan Published',
            'organizer' => 'Disnaker',
            'location' => 'Tasikmalaya',
            'description' => 'Pelatihan untuk masyarakat.',
            'requirements' => 'Warga Kota Tasikmalaya.',
            'poster' => null,
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'registration_info' => 'Pendaftaran melalui website.',
            'registration_link' => 'https://example.com/register',
            'status' => 'published',
        ]);

        Training::create([
            'title' => 'Pelatihan Draft',
            'organizer' => 'Disnaker',
            'location' => 'Tasikmalaya',
            'description' => 'Pelatihan draft.',
            'requirements' => null,
            'poster' => null,
            'start_date' => now()->addDays(14)->toDateString(),
            'end_date' => now()->addDays(17)->toDateString(),
            'registration_info' => null,
            'registration_link' => null,
            'status' => 'draft',
        ]);

        $response = $this->getJson(
            '/api/trainings'
        );

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.title',
            'Pelatihan Published'
        );
    }

    public function test_public_training_detail_cannot_show_draft(): void
    {
        $training = Training::create([
            'title' => 'Pelatihan Draft',
            'organizer' => 'Disnaker',
            'location' => 'Tasikmalaya',
            'description' => 'Pelatihan draft.',
            'requirements' => null,
            'poster' => null,
            'start_date' => now()->addDays(14)->toDateString(),
            'end_date' => now()->addDays(17)->toDateString(),
            'registration_info' => null,
            'registration_link' => null,
            'status' => 'draft',
        ]);

        $response = $this->getJson(
            "/api/trainings/{$training->id}"
        );

        $response->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | PAGES
    |--------------------------------------------------------------------------
    */

    public function test_public_pages_only_shows_published_pages(): void
    {
        Page::create([
            'title' => 'Sejarah',
            'slug' => 'sejarah',
            'content' => 'Isi halaman sejarah.',
            'image' => null,
            'status' => 'published',
        ]);

        Page::create([
            'title' => 'Draft Page',
            'slug' => 'draft-page',
            'content' => 'Isi draft.',
            'image' => null,
            'status' => 'draft',
        ]);

        $response = $this->getJson(
            '/api/pages'
        );

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.slug',
            'sejarah'
        );
    }

    public function test_public_page_can_be_accessed_by_slug(): void
    {
        Page::create([
            'title' => 'Visi dan Misi',
            'slug' => 'visi-misi',
            'content' => 'Isi visi dan misi.',
            'image' => null,
            'status' => 'published',
        ]);

        $response = $this->getJson(
            '/api/pages/visi-misi'
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.slug',
                'visi-misi'
            )
            ->assertJsonPath(
                'data.title',
                'Visi dan Misi'
            );
    }

    public function test_public_draft_page_cannot_be_accessed_by_slug(): void
    {
        Page::create([
            'title' => 'Draft Page',
            'slug' => 'draft-page',
            'content' => 'Isi draft.',
            'image' => null,
            'status' => 'draft',
        ]);

        $response = $this->getJson(
            '/api/pages/draft-page'
        );

        $response->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | SERVICES
    |--------------------------------------------------------------------------
    */

    public function test_public_services_only_shows_published_services(): void
    {
        Service::create([
            'title' => 'Pelayanan AK1',
            'slug' => 'pelayanan-ak1',
            'description' => 'Informasi pelayanan AK1.',
            'requirements' => 'Persyaratan pelayanan.',
            'procedure' => 'Prosedur pelayanan.',
            'image' => null,
            'external_link' => 'https://example.com/ak1',
            'status' => 'published',
        ]);

        Service::create([
            'title' => 'Service Draft',
            'slug' => 'service-draft',
            'description' => 'Service draft.',
            'requirements' => null,
            'procedure' => null,
            'image' => null,
            'external_link' => null,
            'status' => 'draft',
        ]);

        $response = $this->getJson(
            '/api/services'
        );

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.slug',
            'pelayanan-ak1'
        );
    }

    public function test_public_service_can_be_accessed_by_slug(): void
    {
        Service::create([
            'title' => 'Pelayanan AK1',
            'slug' => 'pelayanan-ak1',
            'description' => 'Informasi pelayanan.',
            'requirements' => 'Persyaratan.',
            'procedure' => 'Prosedur.',
            'image' => null,
            'external_link' => 'https://example.com',
            'status' => 'published',
        ]);

        $response = $this->getJson(
            '/api/services/pelayanan-ak1'
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.slug',
                'pelayanan-ak1'
            )
            ->assertJsonPath(
                'data.title',
                'Pelayanan AK1'
            );
    }

    public function test_public_draft_service_cannot_be_accessed_by_slug(): void
    {
        Service::create([
            'title' => 'Service Draft',
            'slug' => 'service-draft',
            'description' => 'Service draft.',
            'requirements' => null,
            'procedure' => null,
            'image' => null,
            'external_link' => null,
            'status' => 'draft',
        ]);

        $response = $this->getJson(
            '/api/services/service-draft'
        );

        $response->assertStatus(404);
    }
}