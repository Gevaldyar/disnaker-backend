<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobSeekerResource;
use App\Models\JobSeeker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminJobSeekerController extends Controller
{
    /**
     * Menampilkan daftar pencari kerja.
     */
    public function index(Request $request)
    {
        $query = JobSeeker::query();

        // Search berdasarkan nama, NIK, atau nomor AK1.
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('ak1_number', 'like', "%{$search}%");
            });
        }

        // Filter status.
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter pendidikan terakhir.
        if ($request->filled('last_education')) {
            $query->where(
                'last_education',
                'like',
                '%' . $request->last_education . '%'
            );
        }

        // Filter lokasi pekerjaan yang diinginkan.
        if ($request->filled('desired_location')) {
            $query->where(
                'desired_location',
                'like',
                '%' . $request->desired_location . '%'
            );
        }

        $jobSeekers = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return JobSeekerResource::collection($jobSeekers);
    }

    /**
     * Menampilkan detail pencari kerja.
     */
    public function show(JobSeeker $jobSeeker): JobSeekerResource
    {
        return new JobSeekerResource($jobSeeker);
    }

    /**
     * Menambahkan data pencari kerja.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ak1_number' => [
                'nullable',
                'string',
                'max:100',
                'unique:job_seekers,ak1_number',
            ],

            'nik' => [
                'required',
                'string',
                'max:50',
                'unique:job_seekers,nik',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'birth_place' => [
                'nullable',
                'string',
                'max:100',
            ],

            'birth_date' => [
                'nullable',
                'date',
            ],

            'gender' => [
                'nullable',
                'string',
                'max:50',
            ],

            'marital_status' => [
                'nullable',
                'string',
                'max:50',
            ],

            'address' => [
                'required',
                'string',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'last_education' => [
                'nullable',
                'string',
                'max:100',
            ],

            'institution' => [
                'nullable',
                'string',
                'max:255',
            ],

            'skills' => [
                'nullable',
                'string',
            ],

            'languages' => [
                'nullable',
                'string',
            ],

            'desired_position' => [
                'nullable',
                'string',
                'max:255',
            ],

            'desired_location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'desired_salary' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'worked_last_6_months' => [
                'nullable',
                'boolean',
            ],
        ]);

        $jobSeeker = JobSeeker::create($validated);

        return response()->json([
            'message' => 'Data pencari kerja berhasil ditambahkan.',
            'data' => new JobSeekerResource($jobSeeker),
        ], 201);
    }

    /**
     * Mengubah data pencari kerja.
     */
    public function update(
        Request $request,
        JobSeeker $jobSeeker
    ): JsonResponse {
        $validated = $request->validate([
            'ak1_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('job_seekers', 'ak1_number')
                    ->ignore($jobSeeker->id),
            ],

            'nik' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('job_seekers', 'nik')
                    ->ignore($jobSeeker->id),
            ],

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'birth_place' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'birth_date' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'gender' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
            ],

            'marital_status' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
            ],

            'address' => [
                'sometimes',
                'required',
                'string',
            ],

            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
            ],

            'last_education' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'institution' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'skills' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'languages' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'desired_position' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'desired_location' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'desired_salary' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],

            'worked_last_6_months' => [
                'sometimes',
                'nullable',
                'boolean',
            ],
        ]);

        $jobSeeker->update($validated);

        return response()->json([
            'message' => 'Data pencari kerja berhasil diperbarui.',
            'data' => new JobSeekerResource($jobSeeker->fresh()),
        ]);
    }

    /**
     * Menghapus data pencari kerja.
     */
    public function destroy(JobSeeker $jobSeeker): JsonResponse
    {
        $jobSeeker->delete();

        return response()->json([
            'message' => 'Data pencari kerja berhasil dihapus.',
        ]);
    }

    /**
     * Memverifikasi pencari kerja.
     */
    public function verify(JobSeeker $jobSeeker): JobSeekerResource
    {
        $jobSeeker->update([
            'status' => 'verified',
            'rejection_reason' => null,
            'verified_at' => now(),
        ]);

        return new JobSeekerResource($jobSeeker->fresh());
    }

    /**
     * Menolak pencari kerja.
     */
    public function reject(
        Request $request,
        JobSeeker $jobSeeker
    ): JsonResponse {
        $validated = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        $jobSeeker->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'verified_at' => now(),
        ]);

        return response()->json([
            'message' => 'Data pencari kerja ditolak.',
            'data' => new JobSeekerResource($jobSeeker->fresh()),
        ]);
    }
}