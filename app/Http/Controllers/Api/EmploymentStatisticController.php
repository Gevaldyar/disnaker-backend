<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Job;
use App\Models\JobSeeker;
use App\Models\Training;
use Illuminate\Http\JsonResponse;

class EmploymentStatisticController extends Controller
{
    /**
     * Display public employment statistics.
     */
    public function index(): JsonResponse
    {
        $jobSeekers = JobSeeker::where('status', 'verified')
            ->count();

        $companies = Company::where('status', 'approved')
            ->count();

        $activeJobs = Job::where('status', 'approved')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', today());
            })
            ->count();

        $expiredJobs = Job::where('status', 'expired')
            ->count();

        $publishedTrainings = Training::where('status', 'published')
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Data ketenagakerjaan berhasil diambil',
            'data' => [
                'job_seekers' => $jobSeekers,
                'companies' => $companies,
                'active_jobs' => $activeJobs,
                'expired_jobs' => $expiredJobs,
                'published_trainings' => $publishedTrainings,
            ],
        ]);
    }
}