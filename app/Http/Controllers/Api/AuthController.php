<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobSeekerProfile;
use App\Models\User;
use App\Notifications\AdminPendingNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Registrasi user baru.
     *
     * Role yang dapat dipilih:
     * - pencari_kerja
     * - perusahaan
     *
     * Role admin tidak dapat dipilih melalui endpoint ini.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role' => [
                'required',
                Rule::in([
                    'pencari_kerja',
                    'perusahaan',
                ]),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            /*
             * Field khusus perusahaan.
             */
            'company_name' => [
                'required_if:role,perusahaan',
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'address' => [
                'required_if:role,perusahaan',
                'nullable',
                'string',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'company_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'website' => [
                'nullable',
                'url',
                'max:255',
            ],
        ]);

        $result = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $validated['role'],
            ]);

            /*
             * REGISTRASI PENCAIr KERJA
             *
             * Akun langsung aktif tanpa menunggu verifikasi Admin.
             */
            if ($validated['role'] === 'pencari_kerja') {
                $profile = JobSeekerProfile::create([
                    'user_id' => $user->id,
                    'full_name' => $validated['name'],
                    'is_public' => false,
                ]);

                return [
                    'user' => $user,
                    'profile' => $profile,
                    'company' => null,
                ];
            }

            /*
             * REGISTRASI PERUSAHAAN
             *
             * Perusahaan tetap harus menunggu verifikasi Admin.
             */
            $company = Company::create([
                'user_id' => $user->id,
                'name' => $validated['company_name'],
                'description' => $validated['description'] ?? null,
                'address' => $validated['address'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['company_email'] ?? null,
                'website' => $validated['website'] ?? null,
                'status' => 'pending',
                'rejection_reason' => null,
                'verified_at' => null,
            ]);

            return [
                'user' => $user,
                'profile' => null,
                'company' => $company,
            ];
        });

        /*
         * Hanya perusahaan yang menunggu
         * tindakan Admin yang mendapatkan notification.
         */
        if ($validated['role'] === 'perusahaan') {
            $admins = User::where('role', 'admin')->get();

            Notification::send(
                $admins,
                new AdminPendingNotification(
                    'company_registered',
                    'Perusahaan Baru',
                    'Ada perusahaan baru yang mendaftar dan menunggu verifikasi.',
                    $result['company']->id,
                    null
                )
            );
        }

        return response()->json([
            'success' => true,
            'message' => $validated['role'] === 'pencari_kerja'
                ? 'Akun pencari kerja berhasil dibuat.'
                : 'Akun perusahaan berhasil dibuat dan menunggu verifikasi Admin.',
            'data' => [
                'user' => $result['user']->fresh(),

                'profile' => $result['profile']
                    ? $result['profile']->fresh()
                    : null,

                'company' => $result['company']
                    ? $result['company']->fresh()
                    : null,
            ],
        ], 201);
    }

    /**
     * Login user.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah',
            ], 401);
        }

        $token = $user->createToken('disnaker-api')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * Display authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil',
            'data' => $request->user(),
        ]);
    }
}