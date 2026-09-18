<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyRegistrationController extends Controller
{
    /**
     * Register a new company account.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            'company_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        $result = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'perusahaan',
            ]);

            $company = Company::create([
                'user_id' => $user->id,
                'name' => $validated['company_name'],
                'description' => $validated['description'] ?? null,
                'address' => $validated['address'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['company_email'] ?? $validated['email'],
                'website' => $validated['website'] ?? null,
                'logo' => null,
                'status' => 'pending',
                'rejection_reason' => null,
                'verified_at' => null,
            ]);

            return [
                'user' => $user,
                'company' => $company,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran perusahaan berhasil. Akun menunggu verifikasi Admin Disnaker.',
            'data' => $result,
        ], 201);
    }
}