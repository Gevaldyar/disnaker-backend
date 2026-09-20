<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    /**
     * Display all published pages.
     */
    public function index(): JsonResponse
    {
        $pages = Page::where('status', 'published')
            ->orderBy('title')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar halaman berhasil diambil',
            'data' => $pages,
        ]);
    }

    /**
     * Display a published page by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $page = Page::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Halaman tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Halaman berhasil diambil',
            'data' => $page,
        ]);
    }
}