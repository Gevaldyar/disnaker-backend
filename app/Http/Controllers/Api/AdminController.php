<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Menampilkan semua admin.
     */
    public function index(): JsonResponse
    {
        $admins = User::query()
            ->where('role', 'admin')
            ->latest()
            ->get([
                'id',
                'name',
                'email',
                'role',
                'created_at',
                'updated_at',
            ]);

        return response()->json([
            'message' => 'Daftar admin berhasil diambil.',
            'data' => $admins,
        ]);
    }

    /**
     * Membuat admin baru.
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
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
        ]);

        return response()->json([
            'message' => 'Admin berhasil dibuat.',
            'data' => $admin->only([
                'id',
                'name',
                'email',
                'role',
                'created_at',
                'updated_at',
            ]),
        ], 201);
    }

    /**
     * Menampilkan detail admin.
     */
    public function show(User $user): JsonResponse
    {
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'User yang diminta bukan admin.',
            ], 404);
        }

        return response()->json([
            'message' => 'Data admin berhasil diambil.',
            'data' => $user->only([
                'id',
                'name',
                'email',
                'role',
                'created_at',
                'updated_at',
            ]),
        ]);
    }

    /**
     * Mengubah data admin.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'User yang diminta bukan admin.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'password' => [
                'sometimes',
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }

        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);

            // Token lama tidak berlaku lagi setelah password berubah.
            $user->tokens()->delete();
        }

        $user->save();

        return response()->json([
            'message' => 'Data admin berhasil diperbarui.',
            'data' => $user->only([
                'id',
                'name',
                'email',
                'role',
                'created_at',
                'updated_at',
            ]),
        ]);
    }

    /**
     * Menghapus admin.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'User yang diminta bukan admin.',
            ], 404);
        }

        // Admin tidak boleh menghapus dirinya sendiri.
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'Admin tidak dapat menghapus akun sendiri.',
            ], 403);
        }

        // Pastikan masih ada minimal satu admin.
        $adminCount = User::where('role', 'admin')->count();

        if ($adminCount <= 1) {
            return response()->json([
                'message' => 'Admin terakhir tidak dapat dihapus.',
            ], 403);
        }

        // Hapus semua token admin yang akan dihapus.
        $user->tokens()->delete();

        $user->delete();

        return response()->json([
            'message' => 'Admin berhasil dihapus.',
        ]);
    }
}