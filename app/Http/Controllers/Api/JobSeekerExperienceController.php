<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobSeekerExperienceResource;
use App\Models\JobSeekerExperience;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobSeekerExperienceController extends Controller
{
    /**
     * Menampilkan semua pengalaman kerja milik user.
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

        $experiences = $profile->experiences()
            ->orderByDesc('is_current')
            ->orderByDesc('end_date')
            ->orderByDesc('start_date')
            ->get();

        return JobSeekerExperienceResource::collection(
            $experiences
        );
    }

    /**
     * Menambahkan pengalaman kerja.
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
            'company_name' => [
                'required',
                'string',
                'max:255',
            ],

            'position' => [
                'required',
                'string',
                'max:255',
            ],

            'employment_type' => [
                'nullable',
                'string',
                'max:50',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
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

        $experience = $profile->experiences()->create([
            'company_name' => $validated['company_name'],
            'position' => $validated['position'],
            'employment_type' =>
                $validated['employment_type'] ?? null,
            'location' =>
                $validated['location'] ?? null,
            'start_date' =>
                $validated['start_date'] ?? null,
            'end_date' =>
                $validated['end_date'] ?? null,
            'is_current' =>
                $validated['is_current'] ?? false,
            'description' =>
                $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengalaman kerja berhasil ditambahkan.',
            'data' => new JobSeekerExperienceResource(
                $experience
            ),
        ], 201);
    }

    /**
     * Mengubah pengalaman kerja milik user.
     */
    public function update(
        Request $request,
        JobSeekerExperience $experience
    ): JsonResponse {
        $profile = $request->user()->jobSeekerProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil pencari kerja belum dibuat.',
            ], 404);
        }

        $ownedExperience = $profile->experiences()
            ->where('id', $experience->id)
            ->first();

        if (!$ownedExperience) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengalaman kerja tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'company_name' => [
                'required',
                'string',
                'max:255',
            ],

            'position' => [
                'required',
                'string',
                'max:255',
            ],

            'employment_type' => [
                'nullable',
                'string',
                'max:50',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
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

        $ownedExperience->update([
            'company_name' => $validated['company_name'],
            'position' => $validated['position'],
            'employment_type' =>
                $validated['employment_type'] ?? null,
            'location' =>
                $validated['location'] ?? null,
            'start_date' =>
                $validated['start_date'] ?? null,
            'end_date' =>
                $validated['end_date'] ?? null,
            'is_current' =>
                $validated['is_current'] ?? false,
            'description' =>
                $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengalaman kerja berhasil diperbarui.',
            'data' => new JobSeekerExperienceResource(
                $ownedExperience->fresh()
            ),
        ]);
    }

    /**
     * Menghapus pengalaman kerja milik user.
     */
    public function destroy(
        Request $request,
        JobSeekerExperience $experience
    ): JsonResponse {
        $profile = $request->user()->jobSeekerProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil pencari kerja belum dibuat.',
            ], 404);
        }

        $ownedExperience = $profile->experiences()
            ->where('id', $experience->id)
            ->first();

        if (!$ownedExperience) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengalaman kerja tidak ditemukan.',
            ], 404);
        }

        $ownedExperience->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengalaman kerja berhasil dihapus.',
        ]);
    }
}