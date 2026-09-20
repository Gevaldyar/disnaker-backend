<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyJobResource;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyJobController extends Controller
{
    /**
     * Display jobs owned by the authenticated company.
     */
    public function index(Request $request)
    {
        $company = $request->user()->company;

        $jobs = $company->jobs()
            ->latest()
            ->paginate(10);

        return CompanyJobResource::collection($jobs)
            ->additional([
                'success' => true,
                'message' => 'Daftar lowongan perusahaan berhasil diambil',
            ]);
    }

    /**
     * Store a newly created job.
     */
    public function store(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        if ($company->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Akun perusahaan belum disetujui oleh Admin Disnaker',
            ], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'poster' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
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

        return (new CompanyJobResource($job->fresh()))
            ->additional([
                'success' => true,
                'message' => 'Lowongan berhasil dibuat',
            ])
            ->response()
            ->setStatusCode(201);
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

        return (new CompanyJobResource($job->load('company')))
            ->additional([
                'success' => true,
                'message' => 'Detail lowongan berhasil diambil',
            ])
            ->response();
    }

    /**
     * Update a job owned by the authenticated company.
     */
    public function update(Request $request, Job $job): JsonResponse
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

        if (in_array($job->status, ['pending', 'expired'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan dengan status ini tidak dapat diedit',
            ], 422);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'poster' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
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
            if ($job->poster) {
                Storage::disk('public')->delete($job->poster);
            }

            $data['poster'] = $request->file('poster')
                ->store('job-posters', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Perubahan lowongan
        |--------------------------------------------------------------------------
        |
        | Jika lowongan sebelumnya sudah approved atau rejected,
        | perubahan akan dikembalikan ke draft agar dapat diproses
        | kembali oleh Admin.
        |
        */

        if (in_array($job->status, ['approved', 'rejected'], true)) {
            $data['status'] = 'draft';
            $data['published_at'] = null;
            $data['verified_at'] = null;
            $data['rejection_reason'] = null;
        }

        $job->update($data);

        return (new CompanyJobResource($job->fresh()))
            ->additional([
                'success' => true,
                'message' => 'Lowongan berhasil diperbarui',
            ])
            ->response();
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

        if (
            $job->expires_at !== null &&
            $job->expires_at->isBefore(today())
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Masa berlaku lowongan sudah lewat',
            ], 422);
        }

        $job->update([
            'status' => 'pending',
            'rejection_reason' => null,
            'verified_at' => null,
        ]);

        return (new CompanyJobResource($job->fresh()))
            ->additional([
                'success' => true,
                'message' => 'Lowongan berhasil diajukan untuk verifikasi Admin',
            ])
            ->response();
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

        if ($job->status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan yang sedang menunggu verifikasi tidak dapat dihapus',
            ], 422);
        }

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