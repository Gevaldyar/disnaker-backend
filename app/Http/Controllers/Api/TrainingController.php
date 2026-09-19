<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Http\JsonResponse;

class TrainingController extends Controller
{
    /**
     * Display published trainings.
     */
    public function index(): JsonResponse
    {
        $trainings = Training::where('status', 'published')
            ->orderBy('start_date')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pelatihan berhasil diambil',
            'data' => $trainings,
        ]);
    }

    /**
     * Display a published training.
     */
    public function show(Training $training): JsonResponse
    {
        if ($training->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Pelatihan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pelatihan berhasil diambil',
            'data' => $training,
        ]);
    }
}