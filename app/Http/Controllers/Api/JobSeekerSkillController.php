<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobSeekerSkillResource;
use App\Models\JobSeekerSkill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobSeekerSkillController extends Controller
{
    /**
     * Menampilkan semua skill milik pencari kerja yang sedang login.
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

        $skills = $profile->skills()
            ->latest()
            ->get();

        return JobSeekerSkillResource::collection($skills);
    }

    /**
     * Menambahkan skill baru.
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
            'skill_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('job_seeker_skills', 'skill_name')
                    ->where(function ($query) use ($profile) {
                        return $query->where(
                            'job_seeker_profile_id',
                            $profile->id
                        );
                    }),
            ],

            'level' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        $skill = $profile->skills()->create([
            'skill_name' => $validated['skill_name'],
            'level' => $validated['level'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Keahlian berhasil ditambahkan.',
            'data' => new JobSeekerSkillResource($skill),
        ], 201);
    }

    /**
     * Mengubah skill milik pencari kerja.
     */
    public function update(
        Request $request,
        JobSeekerSkill $skill
    ): JsonResponse {
        $profile = $request->user()->jobSeekerProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil pencari kerja belum dibuat.',
            ], 404);
        }

        /*
         * Pastikan skill memang milik profile user yang sedang login.
         */
        $ownedSkill = $profile->skills()
            ->where('id', $skill->id)
            ->first();

        if (!$ownedSkill) {
            return response()->json([
                'success' => false,
                'message' => 'Keahlian tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'skill_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('job_seeker_skills', 'skill_name')
                    ->where(function ($query) use ($profile) {
                        return $query->where(
                            'job_seeker_profile_id',
                            $profile->id
                        );
                    })
                    ->ignore($ownedSkill->id),
            ],

            'level' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        $ownedSkill->update([
            'skill_name' => $validated['skill_name'],
            'level' => $validated['level'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Keahlian berhasil diperbarui.',
            'data' => new JobSeekerSkillResource(
                $ownedSkill->fresh()
            ),
        ]);
    }

    /**
     * Menghapus skill milik pencari kerja.
     */
    public function destroy(
        Request $request,
        JobSeekerSkill $skill
    ): JsonResponse {
        $profile = $request->user()->jobSeekerProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil pencari kerja belum dibuat.',
            ], 404);
        }

        $ownedSkill = $profile->skills()
            ->where('id', $skill->id)
            ->first();

        if (!$ownedSkill) {
            return response()->json([
                'success' => false,
                'message' => 'Keahlian tidak ditemukan.',
            ], 404);
        }

        $ownedSkill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Keahlian berhasil dihapus.',
        ]);
    }
}