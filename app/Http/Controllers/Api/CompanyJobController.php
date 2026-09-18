<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyJobController extends Controller
{
    /**
     * Display jobs owned by the authenticated company.
     */
    public function index(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        $jobs = $company->jobs()
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar lowongan perusahaan berhasil diambil',
            'data' => $jobs,
        ]);
    }

    /**
     * Store a newly created job.
     */
    public function store(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $posterPath = null;

        if ($request->hasFile('poster')) {
            $posterPath = $request->file('poster')
                ->store('job-posters', 'public');
        }

        $job = $company->jobs()->create([
            'title' => $validated['title'],
            'poster' => $posterPath,
            'location' => $validated['location'],
            'description' => $validated['description'],
            'published_at' => null,
            'expires_at' => $validated['expires_at'] ?? null,
            'views' => 0,
            'status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dibuat',
            'data' => $job->fresh()->load('company'),
        ], 201);
    }

    /**
     * Display a specific job owned by the authenticated company.
     */
    public function show(Request $request, Job $job): JsonResponse
    {
        $company = $request->user()->company;

        if ($job->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke lowongan ini',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail lowongan berhasil diambil',
            'data' => $job->load('company'),
        ]);
    }

    /**
     * Update a job owned by the authenticated company.
     */
    public function update(Request $request, Job $job): JsonResponse
    {
        $company = $request->user()->company;

        if ($job->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke lowongan ini',
            ], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $data = [
            'title' => $validated['title'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'expires_at' => $validated['expires_at'] ?? null,
        ];

        if ($request->hasFile('poster')) {
            // Hapus poster lama jika ada.
            if ($job->poster) {
                Storage::disk('public')->delete($job->poster);
            }

            $data['poster'] = $request->file('poster')
                ->store('job-posters', 'public');
        }

        $job->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil diperbarui',
            'data' => $job->fresh()->load('company'),
        ]);
    }

    /**
     * Submit a job for admin verification.
     */
    public function submit(Request $request, Job $job): JsonResponse
    {
        $company = $request->user()->company;

        if ($company->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Akun perusahaan belum disetujui oleh Admin Disnaker',
            ], 403);
        }

        if ($job->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke lowongan ini',
            ], 403);
        }

        if ($job->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya lowongan dengan status draft yang dapat diajukan',
            ], 422);
        }

        if ($job->expires_at !== null && $job->expires_at->isBefore(today())) {
            return response()->json([
                'success' => false,
                'message' => 'Masa berlaku lowongan sudah lewat',
            ], 422);
        }

        $job->update([
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil diajukan untuk verifikasi Admin',
            'data' => $job->fresh()->load('company'),
        ]);
    }

    /**
     * Remove a job owned by the authenticated company.
     */
    public function destroy(Request $request, Job $job): JsonResponse
    {
        $company = $request->user()->company;

        if ($company->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Akun perusahaan belum disetujui oleh Admin Disnaker',
            ], 403);
    }

        if ($job->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke lowongan ini',
            ], 403);
        }

        // Hapus poster dari storage jika ada.
        if ($job->poster) {
            Storage::disk('public')->delete($job->poster);
        }

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dihapus',
        ]);
    }
}