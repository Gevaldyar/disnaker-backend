<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCompanyController extends Controller
{
    /**
     * Display all companies.
     */
    public function index(): JsonResponse
    {
        $companies = Company::with('user')
            ->withCount('jobs')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar perusahaan berhasil diambil',
            'data' => $companies,
        ]);
    }

    /**
     * Display companies waiting for verification.
     */
    public function pending(): JsonResponse
    {
        $companies = Company::with('user')
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar perusahaan yang menunggu verifikasi berhasil diambil',
            'data' => $companies,
        ]);
    }

    /**
     * Display a company.
     */
    public function show(Company $company): JsonResponse
    {
        $company->load('user', 'jobs');

        return response()->json([
            'success' => true,
            'message' => 'Detail perusahaan berhasil diambil',
            'data' => $company,
        ]);
    }

    /**
     * Approve a company.
     */
    public function approve(Company $company): JsonResponse
    {
        if ($company->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya perusahaan dengan status pending yang dapat disetujui',
            ], 422);
        }

        $company->update([
            'status' => 'approved',
            'rejection_reason' => null,
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perusahaan berhasil disetujui',
            'data' => $company->fresh()->load('user'),
        ]);
    }

    /**
     * Reject a company.
     */
    public function reject(Request $request, Company $company): JsonResponse
    {
        if ($company->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya perusahaan dengan status pending yang dapat ditolak',
            ], 422);
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5'],
        ]);

        $company->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'verified_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perusahaan berhasil ditolak',
            'data' => $company->fresh()->load('user'),
        ]);
    }

    /**
     * Suspend an approved company.
     */
    public function suspend(Company $company): JsonResponse
    {
        if ($company->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya perusahaan yang sudah approved yang dapat dinonaktifkan',
            ], 422);
        }

        $company->update([
            'status' => 'suspended',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perusahaan berhasil dinonaktifkan',
            'data' => $company->fresh()->load('user'),
        ]);
    }
}