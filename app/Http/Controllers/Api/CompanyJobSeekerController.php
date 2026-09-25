<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobSeekerCompanyResource;
use App\Models\JobSeekerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyJobSeekerController extends Controller
{
    /**
     * Perusahaan melihat detail profil pencari kerja.
     */
    public function show(
        Request $request,
        JobSeekerProfile $jobSeeker
    ): JobSeekerCompanyResource {
        $company = $request->user()->company;

        if (!$company) {
            abort(403, 'Data perusahaan tidak ditemukan.');
        }

        if ($company->status !== 'approved') {
            abort(
                403,
                'Perusahaan belum diverifikasi.'
            );
        }

        if (!$jobSeeker->is_public) {
            abort(404);
        }

        $jobSeeker->load([
            'user',
            'skills',
            'educations',
            'experiences',
        ]);

        return new JobSeekerCompanyResource($jobSeeker);
    }

    /**
     * Perusahaan mengunduh CV pencari kerja.
     */
    public function downloadCv(
        Request $request,
        JobSeekerProfile $jobSeeker
    ) {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Data perusahaan tidak ditemukan.',
            ], 403);
        }

        if ($company->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Perusahaan belum diverifikasi.',
            ], 403);
        }

        if (!$jobSeeker->is_public) {
            return response()->json([
                'success' => false,
                'message' => 'Profil pencari kerja tidak tersedia.',
            ], 404);
        }

        if (!$jobSeeker->cv) {
            return response()->json([
                'success' => false,
                'message' => 'Pencari kerja belum mengunggah CV.',
            ], 404);
        }

        if (!Storage::disk('local')->exists($jobSeeker->cv)) {
            return response()->json([
                'success' => false,
                'message' => 'File CV tidak ditemukan.',
            ], 404);
        }

        return Storage::disk('local')->download(
            $jobSeeker->cv,
            'CV-' . $jobSeeker->full_name . '.' .
            pathinfo(
                $jobSeeker->cv,
                PATHINFO_EXTENSION
            )
        );
    }
}