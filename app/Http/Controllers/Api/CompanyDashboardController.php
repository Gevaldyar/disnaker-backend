<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyJobResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $company = $user->company;

        if (!$company) {
            return response()->json([
                'message' => 'Data perusahaan tidak ditemukan.',
            ], 404);
        }

        $jobsQuery = $company->jobs();

        $latestJobs = $company->jobs()
            ->latest()
            ->take(5)
            ->get();

        $totalJobs = (clone $jobsQuery)->count();

        $draftJobs = (clone $jobsQuery)
            ->where('status', 'draft')
            ->count();

        $pendingJobs = (clone $jobsQuery)
            ->where('status', 'pending')
            ->count();

        $approvedJobs = (clone $jobsQuery)
            ->where('status', 'approved')
            ->count();

        $rejectedJobs = (clone $jobsQuery)
            ->where('status', 'rejected')
            ->count();

        $expiredJobs = (clone $jobsQuery)
            ->where('status', 'expired')
            ->count();

        $activeJobs = (clone $jobsQuery)
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', now()->toDateString());
            })
            ->count();

        return response()->json([
            'message' => 'Data dashboard perusahaan berhasil diambil.',
            'data' => [
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'status' => $company->status,
                    'rejection_reason' => $company->rejection_reason,
                    'verified_at' => $company->verified_at,
                ],

                'statistics' => [
                    'total_jobs' => $totalJobs,
                    'active_jobs' => $activeJobs,
                    'draft_jobs' => $draftJobs,
                    'pending_jobs' => $pendingJobs,
                    'approved_jobs' => $approvedJobs,
                    'rejected_jobs' => $rejectedJobs,
                    'expired_jobs' => $expiredJobs,
                ],

                'latest_jobs' => CompanyJobResource::collection(
                    $latestJobs
                )->resolve($request),
            ],
        ]);
    }
}