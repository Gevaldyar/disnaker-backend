<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Company;
use App\Models\Job;
use App\Models\JobSeeker;
use App\Models\News;
use App\Models\Training;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Statistik perusahaan
        |--------------------------------------------------------------------------
        */

        $companyStatistics = [
            'total' => Company::count(),

            'pending' => Company::where('status', 'pending')
                ->count(),

            'approved' => Company::where('status', 'approved')
                ->count(),

            'rejected' => Company::where('status', 'rejected')
                ->count(),

            'suspended' => Company::where('status', 'suspended')
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Statistik lowongan
        |--------------------------------------------------------------------------
        */

        $jobStatistics = [
            'total' => Job::count(),

            'draft' => Job::where('status', 'draft')
                ->count(),

            'pending' => Job::where('status', 'pending')
                ->count(),

            'approved' => Job::where('status', 'approved')
                ->count(),

            'rejected' => Job::where('status', 'rejected')
                ->count(),

            'expired' => Job::where('status', 'expired')
                ->count(),

            'active' => Job::where('status', 'approved')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhereDate(
                            'expires_at',
                            '>=',
                            now()->toDateString()
                        );
                })
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Statistik konten
        |--------------------------------------------------------------------------
        */

        $contentStatistics = [
            'news_total' => News::count(),

            'news_published' => News::where('status', 'published')
                ->count(),

            'training_total' => Training::count(),

            'training_published' => Training::where('status', 'published')
                ->count(),

            'announcement_total' => Announcement::count(),

            'announcement_published' => Announcement::where('status', 'published')
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Statistik pencari kerja
        |--------------------------------------------------------------------------
        |
        | Data pribadi tidak dikirim ke dashboard.
        | Dashboard hanya menggunakan jumlah data.
        |
        */

        $jobSeekerStatistics = [
            'total' => JobSeeker::count(),

            'pending' => JobSeeker::where('status', 'pending')
                ->count(),

            'verified' => JobSeeker::where('status', 'verified')
                ->count(),

            'rejected' => JobSeeker::where('status', 'rejected')
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Data terbaru
        |--------------------------------------------------------------------------
        */

        $pendingCompanies = Company::query()
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get([
                'id',
                'name',
                'email',
                'phone',
                'status',
                'created_at',
            ]);

        $pendingJobs = Job::query()
            ->with('company:id,name')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get([
                'id',
                'company_id',
                'title',
                'location',
                'expires_at',
                'status',
                'created_at',
            ]);

        $latestNews = News::query()
            ->latest()
            ->take(5)
            ->get([
                'id',
                'title',
                'status',
                'published_at',
                'created_at',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Statistik notifikasi Admin
            |--------------------------------------------------------------------------
            */

            $notificationStatistics = [
                'unread' => $request->user()
                    ->unreadNotifications()
            ->count(),
            ];

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard admin berhasil diambil.',

            'data' => [
                'companies' => $companyStatistics,

                'jobs' => $jobStatistics,

                'job_seekers' => $jobSeekerStatistics,

                'content' => $contentStatistics,

                'notifications' => $notificationStatistics,

                'pending_companies' => $pendingCompanies,

                'pending_jobs' => $pendingJobs,

                'latest_news' => $latestNews,
            ],
        ]);
    }
}