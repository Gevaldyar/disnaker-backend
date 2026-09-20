<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    /**
     * Display published services.
     */
    public function index(): JsonResponse
    {
        $services = Service::where('status', 'published')
            ->orderBy('title')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar layanan berhasil diambil',
            'data' => $services,
        ]);
    }

    /**
     * Display a published service by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $service = Service::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail layanan berhasil diambil',
            'data' => $service,
        ]);
    }
}