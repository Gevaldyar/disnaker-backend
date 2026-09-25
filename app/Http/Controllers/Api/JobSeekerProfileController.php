<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobSeekerProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobSeekerProfileController extends Controller
{
    /**
     * Menampilkan profil pencari kerja yang sedang login.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $profile = $user->jobSeekerProfile?->load([
            'skills',
            'educations',
            'experiences',
        ]);

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil pencari kerja belum dibuat.',
            ], 404);
        }

        return (new JobSeekerProfileResource($profile))
            ->additional([
                'success' => true,
                'message' => 'Profil pencari kerja berhasil diambil.',
            ])
            ->response();
    }

    /**
     * Membuat atau memperbarui profil.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        /*
         * Normalisasi is_public.
         *
         * Form-data dari Postman/browser sering mengirim
         * boolean sebagai string "true"/"false".
         */
        if ($request->has('is_public')) {
            $value = $request->input('is_public');

            if ($value === 'true' || $value === '1' || $value === 1) {
                $request->merge([
                    'is_public' => true,
                ]);
            } elseif ($value === 'false' || $value === '0' || $value === 0) {
                $request->merge([
                    'is_public' => false,
                ]);
            }
        }

        $validated = $request->validate([
            'full_name' => [
                'required',
                'string',
                'max:255',
            ],

            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'cv' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:5120',
            ],

            'headline' => [
                'nullable',
                'string',
                'max:255',
            ],

            'bio' => [
                'nullable',
                'string',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'birth_date' => [
                'nullable',
                'date',
            ],

            'gender' => [
                'nullable',
                'string',
                'max:30',
            ],

            'portfolio_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'linkedin_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'is_public' => [
                'nullable',
                'boolean',
            ],
        ]);

        $profile = $user->jobSeekerProfile;

        /*
         * Jika profile belum ada, buat baru.
         */
        if (!$profile) {
            $profile = $user->jobSeekerProfile()->create([
                'full_name' => $validated['full_name'],
                'headline' => $validated['headline'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'city' => $validated['city'] ?? null,
                'address' => $validated['address'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'portfolio_url' => $validated['portfolio_url'] ?? null,
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'is_public' => $validated['is_public'] ?? true,
            ]);
        } else {
            $profile->update([
                'full_name' => $validated['full_name'],
                'headline' => $validated['headline'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'city' => $validated['city'] ?? null,
                'address' => $validated['address'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'portfolio_url' => $validated['portfolio_url'] ?? null,
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'is_public' =>
                    array_key_exists('is_public', $validated)
                        ? $validated['is_public']
                        : $profile->is_public,
            ]);
        }

        /*
         * Upload foto baru.
         */
        if ($request->hasFile('photo')) {
            if ($profile->photo) {
                Storage::disk('public')->delete($profile->photo);
            }

            $profile->update([
                'photo' => $request->file('photo')->store(
                    'job-seekers/photos',
                    'public'
                ),
            ]);
        }

        /*
         * Upload CV baru.
         */
        if ($request->hasFile('cv')) {
            if ($profile->cv) {
                Storage::disk('local')->delete($profile->cv);
            }

            $profile->update([
                'cv' => $request->file('cv')->store(
                    'job-seekers/cv',
                    'local'
                ),
            ]);
        }

        $profile->load([
            'skills',
            'educations',
            'experiences',
        ]);

        return (new JobSeekerProfileResource($profile->fresh()))
            ->additional([
                'success' => true,
                'message' => 'Profil pencari kerja berhasil disimpan.',
            ])
            ->response();
    }

    /**
 * Download CV milik pencari kerja yang sedang login.
 */
public function downloadCv(Request $request)
{
    $profile = $request->user()->jobSeekerProfile;

    if (!$profile) {
        return response()->json([
            'success' => false,
            'message' => 'Profil pencari kerja belum dibuat.',
        ], 404);
    }

    if (!$profile->cv) {
        return response()->json([
            'success' => false,
            'message' => 'CV belum diunggah.',
        ], 404);
    }

    if (!Storage::disk('local')->exists($profile->cv)) {
        return response()->json([
            'success' => false,
            'message' => 'File CV tidak ditemukan.',
        ], 404);
    }

    return Storage::disk('local')->download(
        $profile->cv,
        'CV-' . $profile->full_name . '.' .
        pathinfo($profile->cv, PATHINFO_EXTENSION)
    );
    }
    
    /**
     * Menghapus profil pencari kerja.
     */
    public function destroy(Request $request): JsonResponse
    {
        $profile = $request->user()->jobSeekerProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil pencari kerja tidak ditemukan.',
            ], 404);
        }

        if ($profile->photo) {
            Storage::disk('public')->delete($profile->photo);
        }

        if ($profile->cv) {
            Storage::disk('local')->delete($profile->cv);
        }

        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profil pencari kerja berhasil dihapus.',
        ]);

    
    }
}