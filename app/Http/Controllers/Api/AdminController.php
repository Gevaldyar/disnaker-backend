<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display all admin accounts.
     */
    public function index(): JsonResponse
    {
        $admins = User::where('role', 'admin')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar Admin berhasil diambil',
            'data' => $admins,
        ]);
    }

    /**
     * Store a new admin account.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Admin berhasil ditambahkan',
            'data' => $admin,
        ], 201);
    }

    /**
     * Display a specific admin.
     */
    public function show(User $user): JsonResponse
    {
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Data Admin tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Admin berhasil diambil',
            'data' => $user,
        ]);
    }

    /**
     * Update an admin account.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Data Admin tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data Admin berhasil diperbarui',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Delete an admin account.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Data Admin tidak ditemukan',
            ], 404);
        }

        // Admin tidak boleh menghapus akun sendiri.
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun Admin yang sedang digunakan',
            ], 422);
        }

        // Jangan sampai sistem tidak memiliki Admin sama sekali.
        $adminCount = User::where('role', 'admin')->count();

        if ($adminCount <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Admin terakhir tidak dapat dihapus',
            ], 422);
        }

        // Cabut token Admin yang akan dihapus.
        $user->tokens()->delete();

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin berhasil dihapus',
        ]);
    }
}