<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminJobSeekerController extends Controller
{
    /**
     * Display a listing of job seekers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobSeeker::query();

        // Pencarian berdasarkan nama, NIK, atau nomor AK-1.
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('ak1_number', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan status.
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter berdasarkan pendidikan terakhir.
        if ($request->filled('education')) {
            $query->where(
                'last_education',
                'like',
                '%' . $request->input('education') . '%'
            );
        }

        // Filter berdasarkan lokasi yang diinginkan.
        if ($request->filled('location')) {
            $query->where(
                'desired_location',
                'like',
                '%' . $request->input('location') . '%'
            );
        }

        $jobSeekers = $query
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pencari kerja berhasil diambil',
            'data' => $jobSeekers,
        ]);
    }

    /**
     * Display a specific job seeker.
     */
    public function show(JobSeeker $jobSeeker): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail pencari kerja berhasil diambil',
            'data' => $jobSeeker,
        ]);
    }

    /**
     * Store a new job seeker.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ak1_number' => ['nullable', 'string', 'max:100', 'unique:job_seekers,ak1_number'],
            'nik' => ['required', 'digits:16', 'unique:job_seekers,nik'],

            'name' => ['required', 'string', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],

            'gender' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['nullable', 'string', 'max:100'],

            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],

            'last_education' => ['nullable', 'string', 'max:100'],
            'institution' => ['nullable', 'string', 'max:255'],

            'skills' => ['nullable', 'string'],
            'languages' => ['nullable', 'string'],

            'desired_position' => ['nullable', 'string', 'max:255'],
            'desired_location' => ['nullable', 'string', 'max:255'],
            'desired_salary' => ['nullable', 'string', 'max:100'],

            'worked_last_6_months' => ['boolean'],
        ]);

        $jobSeeker = JobSeeker::create([
            'ak1_number' => $validated['ak1_number'] ?? null,
            'nik' => $validated['nik'],
            'name' => $validated['name'],
            'birth_place' => $validated['birth_place'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'last_education' => $validated['last_education'] ?? null,
            'institution' => $validated['institution'] ?? null,
            'skills' => $validated['skills'] ?? null,
            'languages' => $validated['languages'] ?? null,
            'desired_position' => $validated['desired_position'] ?? null,
            'desired_location' => $validated['desired_location'] ?? null,
            'desired_salary' => $validated['desired_salary'] ?? null,
            'worked_last_6_months' => $validated['worked_last_6_months'] ?? false,
            'status' => 'pending',
            'rejection_reason' => null,
            'verified_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pencari kerja berhasil ditambahkan',
            'data' => $jobSeeker,
        ], 201);
    }

    /**
     * Update a job seeker.
     */
    public function update(Request $request, JobSeeker $jobSeeker): JsonResponse
    {
        $validated = $request->validate([
            'ak1_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('job_seekers', 'ak1_number')
                    ->ignore($jobSeeker->id),
            ],

            'nik' => [
                'required',
                'digits:16',
                Rule::unique('job_seekers', 'nik')
                    ->ignore($jobSeeker->id),
            ],

            'name' => ['required', 'string', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],

            'gender' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['nullable', 'string', 'max:100'],

            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],

            'last_education' => ['nullable', 'string', 'max:100'],
            'institution' => ['nullable', 'string', 'max:255'],

            'skills' => ['nullable', 'string'],
            'languages' => ['nullable', 'string'],

            'desired_position' => ['nullable', 'string', 'max:255'],
            'desired_location' => ['nullable', 'string', 'max:255'],
            'desired_salary' => ['nullable', 'string', 'max:100'],

            'worked_last_6_months' => ['boolean'],
        ]);

        $jobSeeker->update([
            'ak1_number' => $validated['ak1_number'] ?? null,
            'nik' => $validated['nik'],
            'name' => $validated['name'],
            'birth_place' => $validated['birth_place'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'last_education' => $validated['last_education'] ?? null,
            'institution' => $validated['institution'] ?? null,
            'skills' => $validated['skills'] ?? null,
            'languages' => $validated['languages'] ?? null,
            'desired_position' => $validated['desired_position'] ?? null,
            'desired_location' => $validated['desired_location'] ?? null,
            'desired_salary' => $validated['desired_salary'] ?? null,
            'worked_last_6_months' => $validated['worked_last_6_months'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pencari kerja berhasil diperbarui',
            'data' => $jobSeeker->fresh(),
        ]);
    }

    /**
     * Verify a job seeker.
     */
    public function verify(JobSeeker $jobSeeker): JsonResponse
    {
        if ($jobSeeker->status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Data pencari kerja sudah diverifikasi',
            ], 422);
        }

        $jobSeeker->update([
            'status' => 'verified',
            'rejection_reason' => null,
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pencari kerja berhasil diverifikasi',
            'data' => $jobSeeker->fresh(),
        ]);
    }

    /**
     * Reject a job seeker.
     */
    public function reject(Request $request, JobSeeker $jobSeeker): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5'],
        ]);

        $jobSeeker->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'verified_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pencari kerja berhasil ditolak',
            'data' => $jobSeeker->fresh(),
        ]);
    }

    /**
     * Delete a job seeker.
     */
    public function destroy(JobSeeker $jobSeeker): JsonResponse
    {
        $jobSeeker->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pencari kerja berhasil dihapus',
        ]);
    }
}