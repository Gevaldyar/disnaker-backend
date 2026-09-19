<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    /**
     * Display the authenticated company's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Profil perusahaan belum tersedia',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil perusahaan berhasil diambil',
            'data' => $company,
        ]);
    }

    /**
     * Update the authenticated company's profile.
     */
    public function update(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Profil perusahaan belum tersedia',
            ], 404);
        }

        if ($company->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'Akun perusahaan sedang dinonaktifkan',
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        $company->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'website' => $validated['website'] ?? null,

            // Setiap perubahan profil perlu diverifikasi kembali oleh Admin.
            'status' => 'pending',
            'rejection_reason' => null,
            'verified_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui dan menunggu verifikasi Admin',
            'data' => $company->fresh(),
        ]);
    }

    /**
     * Change the authenticated company's password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password saat ini salah',
            ], 422);
        }

        $user->update([
            'password' => $validated['password'],
        ]);

        // Cabut seluruh token agar login lama tidak tetap aktif.
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah. Silakan login kembali.',
        ]);
    }

    /**
     * Delete the authenticated company's account.
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();
        $company = $user->company;

        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah',
            ], 422);
        }

        // Hapus file-file milik perusahaan sebelum data dihapus.
        if ($company) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }

            foreach ($company->jobs()->whereNotNull('poster')->get() as $job) {
                Storage::disk('public')->delete($job->poster);
            }
        }

        // Hapus seluruh token login.
        $user->tokens()->delete();

        // Company dan jobs akan ikut terhapus melalui foreign key cascade.
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun perusahaan berhasil dihapus',
        ]);
    }
}