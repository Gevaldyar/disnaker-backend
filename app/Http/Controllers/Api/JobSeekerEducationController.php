<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobSeekerEducationResource;
use App\Models\JobSeekerEducation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobSeekerEducationController extends Controller
{
    /**
     * Menampilkan semua pendidikan milik user.
     */
    public function index(Request $request)
    {
        $profile = $request->user()->jobSeekerProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil pencari kerja belum dibuat.',
            ], 404);
        }

        $educations = $profile->educations()
            ->orderByDesc('is_current')
            ->orderByDesc('end_date')
            ->orderByDesc('start_date')
            ->get();

        return JobSeekerEducationResource::collection(
            $educations
        );
    }

    /**
     * Menambahkan pendidikan.
     */
    public function store(Request $request): JsonResponse
    {
        $profile = $request->user()->jobSeekerProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil pencari kerja belum dibuat.',
            ], 404);
        }

        $validated = $request->validate([
            'institution' => [
                'required',
                'string',
                'max:255',
            ],

            'degree' => [
                'nullable',
                'string',
                'max:100',
            ],

            'field_of_study' => [
                'nullable',
                'string',
                'max:150',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'is_current' => [
                'nullable',
                'boolean',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        /*
         * Jika pendidikan masih berjalan,
         * end_date tidak diperlukan.
         */
        if (($validated['is_current'] ?? false) === true) {
            $validated['end_date'] = null;
        }

        $education = $profile->educations()->create([
            'institution' => $validated['institution'],
            'degree' => $validated['degree'] ?? null,
            'field_of_study' => $validated['field_of_study'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_current' => $validated['is_current'] ?? false,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pendidikan berhasil ditambahkan.',
            'data' => new JobSeekerEducationResource($education),
        ], 201);
    }

    /**
     * Mengubah pendidikan milik user.
     */
    public function update(
        Request $request,
        JobSeekerEducation $education
    ): JsonResponse {
        $profile = $request->user()->jobSeekerProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil pencari kerja belum dibuat.',
            ], 404);
        }

        /*
         * Pastikan pendidikan benar-benar
         * milik profile user yang login.
         */
        $ownedEducation = $profile->educations()
            ->where('id', $education->id)
            ->first();

        if (!$ownedEducation) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendidikan tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'institution' => [
                'required',
                'string',
                'max:255',
            ],

            'degree' => [
                'nullable',
                'string',
                'max:100',
            ],

            'field_of_study' => [
                'nullable',
                'string',
                'max:150',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'is_current' => [
                'nullable',
                'boolean',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        if (($validated['is_current'] ?? false) === true) {
            $validated['end_date'] = null;
        }

        $ownedEducation->update([
            'institution' => $validated['institution'],
            'degree' => $validated['degree'] ?? null,
            'field_of_study' => $validated['field_of_study'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_current' => $validated['is_current'] ?? false,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pendidikan berhasil diperbarui.',
            'data' => new JobSeekerEducationResource(
                $ownedEducation->fresh()
            ),
        ]);
    }

    /**
     * Menghapus pendidikan milik user.
     */
    public function destroy(
        Request $request,
        JobSeekerEducation $education
    ): JsonResponse {
        $profile = $request->user()->jobSeekerProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil pencari kerja belum dibuat.',
            ], 404);
        }

        $ownedEducation = $profile->educations()
            ->where('id', $education->id)
            ->first();

        if (!$ownedEducation) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendidikan tidak ditemukan.',
            ], 404);
        }

        $ownedEducation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pendidikan berhasil dihapus.',
        ]);
    }
}