<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\JsonResponse;

class NewsController extends Controller
{
    /**
     * Display published news.
     */
    public function index(): JsonResponse
    {
        $news = News::where('status', 'published')
            ->latest('published_at')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar berita berhasil diambil',
            'data' => $news,
        ]);
    }

    /**
     * Display a published news.
     */
    public function show(News $news): JsonResponse
    {
        if ($news->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail berita berhasil diambil',
            'data' => $news,
        ]);
    }
}