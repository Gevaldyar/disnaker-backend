<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Job;
use App\Models\News;
use App\Models\Training;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    /**
     * Display admin dashboard statistics.
     */
    public function index(): JsonResponse
    {
        // Statistik perusahaan.
        $companyStats = [
            'total' => Company::count(),
            'pending' => Company::where('status', 'pending')->count(),
            'approved' => Company::where('status', 'approved')->count(),
            'rejected' => Company::where('status', 'rejected')->count(),
            'suspended' => Company::where('status', 'suspended')->count(),
        ];

        // Statistik lowongan.
        $jobStats = [
            'total' => Job::count(),

            // Lowongan approved yang belum melewati masa berlaku.
            'active' => Job::where('status', 'approved')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhereDate('expires_at', '>=', today());
                })
                ->count(),

            'pending' => Job::where('status', 'pending')->count(),
            'rejected' => Job::where('status', 'rejected')->count(),
            'expired' => Job::where('status', 'expired')->count(),
            'draft' => Job::where('status', 'draft')->count(),
        ];

        // Statistik berita.
        $newsStats = [
            'total' => News::count(),
            'published' => News::where('status', 'published')->count(),
            'draft' => News::where('status', 'draft')->count(),
        ];

        // Statistik pelatihan.
        $trainingStats = [
            'total' => Training::count(),
            'published' => Training::where('status', 'published')->count(),
            'draft' => Training::where('status', 'draft')->count(),
        ];

        // Lowongan terbaru yang menunggu verifikasi.
        $pendingJobs = Job::with('company')
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        // Perusahaan terbaru yang menunggu verifikasi.
        $pendingCompanies = Company::with('user')
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        // Berita terbaru yang sudah dipublikasikan.
        $latestNews = News::where('status', 'published')
            ->latest('published_at')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard Admin berhasil diambil',
            'data' => [
                'companies' => $companyStats,
                'jobs' => $jobStats,
                'news' => $newsStats,
                'trainings' => $trainingStats,
                'pending_jobs' => $pendingJobs,
                'pending_companies' => $pendingCompanies,
                'latest_news' => $latestNews,
            ],
        ]);
    }
}