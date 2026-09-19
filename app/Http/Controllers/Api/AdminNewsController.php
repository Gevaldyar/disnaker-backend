<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminNewsController extends Controller
{
    /**
     * Display all news.
     */
    public function index(): JsonResponse
    {
        $news = News::latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar berita berhasil diambil',
            'data' => $news,
        ]);
    }

    /**
     * Store a new news.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'instagram_url' => ['required', 'url', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $thumbnailPath = null;

        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')
                ->store('news', 'public');
        }

        $news = News::create([
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'thumbnail' => $thumbnailPath,
            'instagram_url' => $validated['instagram_url'],
            'published_at' => $validated['published_at'] ?? null,
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil dibuat',
            'data' => $news,
        ], 201);
    }

    /**
     * Display a news.
     */
    public function show(News $news): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail berita berhasil diambil',
            'data' => $news,
        ]);
    }

    /**
     * Update a news.
     */
    public function update(Request $request, News $news): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'instagram_url' => ['required', 'url', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $data = [
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'instagram_url' => $validated['instagram_url'],
            'published_at' => $validated['published_at'] ?? null,
            'status' => $validated['status'],
        ];

        if ($request->hasFile('thumbnail')) {
            if ($news->thumbnail) {
                Storage::disk('public')->delete($news->thumbnail);
            }

            $data['thumbnail'] = $request->file('thumbnail')
                ->store('news', 'public');
        }

        $news->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil diperbarui',
            'data' => $news->fresh(),
        ]);
    }

    /**
     * Delete a news.
     */
    public function destroy(News $news): JsonResponse
    {
        if ($news->thumbnail) {
            Storage::disk('public')->delete($news->thumbnail);
        }

        $news->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil dihapus',
        ]);
    }
}