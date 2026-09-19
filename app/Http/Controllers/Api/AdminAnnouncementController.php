<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminAnnouncementController extends Controller
{
    /**
     * Display all announcements.
     */
    public function index(): JsonResponse
    {
        $announcements = Announcement::latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengumuman berhasil diambil',
            'data' => $announcements,
        ]);
    }

    /**
     * Store a new announcement.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'link' => ['nullable', 'url', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $thumbnailPath = null;

        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')
                ->store('announcements', 'public');
        }

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'thumbnail' => $thumbnailPath,
            'link' => $validated['link'] ?? null,
            'published_at' => $validated['published_at'] ?? null,
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dibuat',
            'data' => $announcement,
        ], 201);
    }

    /**
     * Display an announcement.
     */
    public function show(Announcement $announcement): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail pengumuman berhasil diambil',
            'data' => $announcement,
        ]);
    }

    /**
     * Update an announcement.
     */
    public function update(
        Request $request,
        Announcement $announcement
    ): JsonResponse {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'link' => ['nullable', 'url', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $data = [
            'title' => $validated['title'],
            'content' => $validated['content'],
            'link' => $validated['link'] ?? null,
            'published_at' => $validated['published_at'] ?? null,
            'status' => $validated['status'],
        ];

        if ($request->hasFile('thumbnail')) {
            if ($announcement->thumbnail) {
                Storage::disk('public')->delete($announcement->thumbnail);
            }

            $data['thumbnail'] = $request->file('thumbnail')
                ->store('announcements', 'public');
        }

        $announcement->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil diperbarui',
            'data' => $announcement->fresh(),
        ]);
    }

    /**
     * Delete an announcement.
     */
    public function destroy(Announcement $announcement): JsonResponse
    {
        if ($announcement->thumbnail) {
            Storage::disk('public')->delete($announcement->thumbnail);
        }

        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dihapus',
        ]);
    }
}