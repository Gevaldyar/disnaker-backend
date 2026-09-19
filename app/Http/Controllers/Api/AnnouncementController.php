<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    /**
     * Display published announcements.
     */
    public function index(): JsonResponse
    {
        $announcements = Announcement::where('status', 'published')
            ->latest('published_at')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengumuman berhasil diambil',
            'data' => $announcements,
        ]);
    }

    /**
     * Display a published announcement.
     */
    public function show(Announcement $announcement): JsonResponse
    {
        if ($announcement->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Pengumuman tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pengumuman berhasil diambil',
            'data' => $announcement,
        ]);
    }
}