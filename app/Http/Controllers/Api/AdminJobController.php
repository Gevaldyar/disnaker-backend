<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminJobController extends Controller
{
    /**
     * Display all jobs.
     */
    public function index(): JsonResponse
    {
        $jobs = Job::with('company')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar seluruh lowongan berhasil diambil',
            'data' => $jobs,
        ]);
    }

    /**
     * Display jobs waiting for verification.
     */
    public function pending(): JsonResponse
    {
        $jobs = Job::with('company')
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar lowongan yang menunggu verifikasi berhasil diambil',
            'data' => $jobs,
        ]);
    }

    /**
     * Approve a job.
     */
    public function approve(Job $job): JsonResponse
    {
        if ($job->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya lowongan berstatus pending yang dapat disetujui',
            ], 422);
        }

        $job->update([
            'status' => 'approved',
            'rejection_reason' => null,
            'verified_at' => now(),
            'published_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil disetujui',
            'data' => $job->fresh()->load('company'),
        ]);
    }

    /**
     * Reject a job.
     */
    public function reject(Request $request, Job $job): JsonResponse
    {
        if ($job->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya lowongan berstatus pending yang dapat ditolak',
            ], 422);
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5'],
        ]);

        $job->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'verified_at' => now(),
            'published_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil ditolak',
            'data' => $job->fresh()->load('company'),
        ]);
    }

    /**
     * Delete a job.
     */
    public function destroy(Job $job): JsonResponse
    {
        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dihapus oleh Admin',
        ]);
    }
}