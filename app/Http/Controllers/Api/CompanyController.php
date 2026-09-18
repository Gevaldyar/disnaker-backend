<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display the authenticated company's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Profil perusahaan belum tersedia',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil perusahaan berhasil diambil',
            'data' => $company,
        ]);
    }
}