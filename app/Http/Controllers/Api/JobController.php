<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobResource;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * Display a listing of approved and active jobs.
     */
    public function index(Request $request)
    {
        $query = Job::with('company')
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', today());
            });

        // Search berdasarkan judul atau nama perusahaan.
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhereHas('company', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter berdasarkan lokasi.
        if ($request->filled('location')) {
            $location = $request->input('location');

            $query->where(
                'location',
                'like',
                "%{$location}%"
            );
        }

        $jobs = $query
            ->latest('published_at')
            ->paginate(10);

        return JobResource::collection($jobs)
            ->additional([
                'success' => true,
                'message' => 'Daftar lowongan berhasil diambil',
            ]);
    }

    /**
     * Display the specified approved and active job.
     */
    public function show(Job $job)
    {
        // Lowongan yang tidak disetujui tidak boleh ditampilkan ke publik.
        if ($job->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan tidak ditemukan',
            ], 404);
        }

        // Lowongan yang sudah melewati masa berlaku tidak ditampilkan.
        if (
            $job->expires_at !== null &&
            $job->expires_at->isBefore(today())
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan sudah tidak tersedia',
            ], 404);
        }

        // Tambahkan jumlah views.
        $job->increment('views');

        $job->load('company');

        return (new JobResource($job))
            ->additional([
                'success' => true,
                'message' => 'Detail lowongan berhasil diambil',
            ]);
    }
}