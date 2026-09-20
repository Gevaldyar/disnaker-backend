<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminServiceController extends Controller
{
    /**
     * Display all services.
     */
    public function index(): JsonResponse
    {
        $services = Service::latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar layanan berhasil diambil',
            'data' => $services,
        ]);
    }

    /**
     * Store a new service.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:services,slug'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'procedure' => ['nullable', 'string'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'external_link' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('services', 'public');
        }

        $service = Service::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'procedure' => $validated['procedure'] ?? null,
            'image' => $imagePath,
            'external_link' => $validated['external_link'] ?? null,
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil dibuat',
            'data' => $service,
        ], 201);
    }

    /**
     * Display a service.
     */
    public function show(Service $service): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail layanan berhasil diambil',
            'data' => $service,
        ]);
    }

    /**
     * Update a service.
     */
    public function update(Request $request, Service $service): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'slug')->ignore($service->id),
            ],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'procedure' => ['nullable', 'string'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'external_link' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'procedure' => $validated['procedure'] ?? null,
            'external_link' => $validated['external_link'] ?? null,
            'status' => $validated['status'],
        ];

        if ($request->hasFile('image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }

            $data['image'] = $request->file('image')
                ->store('services', 'public');
        }

        $service->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil diperbarui',
            'data' => $service->fresh(),
        ]);
    }

    /**
     * Delete a service.
     */
    public function destroy(Service $service): JsonResponse
    {
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil dihapus',
        ]);
    }
}