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
     * Display the authenticated job seeker's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $profile = $user->jobSeekerProfile?->load('skills');

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil pencari kerja belum dibuat',
            ], 404);
        }

        return (new JobSeekerProfileResource($profile))
            ->additional([
                'success' => true,
                'message' => 'Profil pencari kerja berhasil diambil',
            ])
            ->response();
    }

    /**
     * Create or update the authenticated job seeker's profile.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],

            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],

            'phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],

            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:30'],

            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],

            'is_public' => ['nullable', 'boolean'],
        ]);

        $profile = $user->jobSeekerProfile?->load('skills');

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
                'is_public' => $validated['is_public'] ?? $profile->is_public,
            ]);
        }

        if ($request->hasFile('photo')) {
            if ($profile->photo) {
                Storage::disk('public')->delete($profile->photo);
            }

            $profile->update([
                'photo' => $request->file('photo')
                    ->store('job-seekers/photos', 'public'),
            ]);
        }

        return (new JobSeekerProfileResource($profile->fresh()))
            ->additional([
                'success' => true,
                'message' => 'Profil pencari kerja berhasil disimpan',
            ])
            ->response();
    }

    /**
     * Delete the authenticated job seeker's profile.
     */
    public function destroy(Request $request): JsonResponse
    {
        $profile = $request->user()->jobSeekerProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil pencari kerja tidak ditemukan',
            ], 404);
        }

        if ($profile->photo) {
            Storage::disk('public')->delete($profile->photo);
        }

        if ($profile->cv) {
            Storage::disk('public')->delete($profile->cv);
        }

        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profil pencari kerja berhasil dihapus',
        ]);
    }
}