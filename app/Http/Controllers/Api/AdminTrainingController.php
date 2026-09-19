<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminTrainingController extends Controller
{
    /**
     * Display all trainings.
     */
    public function index(): JsonResponse
    {
        $trainings = Training::latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pelatihan berhasil diambil',
            'data' => $trainings,
        ]);
    }

    /**
     * Store a new training.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'organizer' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'registration_info' => ['nullable', 'string'],
            'registration_link' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $posterPath = null;

        if ($request->hasFile('poster')) {
            $posterPath = $request->file('poster')
                ->store('trainings', 'public');
        }

        $training = Training::create([
            'title' => $validated['title'],
            'organizer' => $validated['organizer'] ?? null,
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'poster' => $posterPath,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'registration_info' => $validated['registration_info'] ?? null,
            'registration_link' => $validated['registration_link'] ?? null,
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pelatihan berhasil dibuat',
            'data' => $training,
        ], 201);
    }

    /**
     * Display a training.
     */
    public function show(Training $training): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail pelatihan berhasil diambil',
            'data' => $training,
        ]);
    }

    /**
     * Update a training.
     */
    public function update(Request $request, Training $training): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'organizer' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'registration_info' => ['nullable', 'string'],
            'registration_link' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $data = [
            'title' => $validated['title'],
            'organizer' => $validated['organizer'] ?? null,
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'registration_info' => $validated['registration_info'] ?? null,
            'registration_link' => $validated['registration_link'] ?? null,
            'status' => $validated['status'],
        ];

        if ($request->hasFile('poster')) {
            if ($training->poster) {
                Storage::disk('public')->delete($training->poster);
            }

            $data['poster'] = $request->file('poster')
                ->store('trainings', 'public');
        }

        $training->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Pelatihan berhasil diperbarui',
            'data' => $training->fresh(),
        ]);
    }

    /**
     * Delete a training.
     */
    public function destroy(Training $training): JsonResponse
    {
        if ($training->poster) {
            Storage::disk('public')->delete($training->poster);
        }

        $training->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pelatihan berhasil dihapus',
        ]);
    }
}