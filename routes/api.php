<?php

use App\Http\Controllers\Api\CompanyRegistrationController;
use App\Http\Controllers\Api\CompanyJobController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\JobController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminJobController;
use App\Http\Controllers\Api\AdminCompanyController;

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API Disnaker berhasil berjalan'
    ]);
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/{job}', [JobController::class, 'show']);

Route::post('/company/register', [CompanyRegistrationController::class, 'register']);

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
Route::middleware('role:admin')->group(function () {
    Route::get('/admin/jobs', [AdminJobController::class, 'index']);
    Route::get('/admin/jobs/pending', [AdminJobController::class, 'pending']);

    Route::patch('/admin/jobs/{job}/approve', [AdminJobController::class, 'approve']);
    Route::patch('/admin/jobs/{job}/reject', [AdminJobController::class, 'reject']);

    Route::delete('/admin/jobs/{job}', [AdminJobController::class, 'destroy']);

    Route::get('/admin/companies', [AdminCompanyController::class, 'index']);
    Route::get('/admin/companies/pending', [AdminCompanyController::class, 'pending']);
    Route::get('/admin/companies/{company}', [AdminCompanyController::class, 'show']);

    Route::patch('/admin/companies/{company}/approve', [AdminCompanyController::class, 'approve']);
    Route::patch('/admin/companies/{company}/reject', [AdminCompanyController::class, 'reject']);
    Route::patch('/admin/companies/{company}/suspend', [AdminCompanyController::class, 'suspend']);
});

    /*
    |--------------------------------------------------------------------------
    | Company Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:perusahaan')->group(function () {
        Route::get('/company/profile', [CompanyController::class, 'show']);

        Route::get('/company/jobs', [CompanyJobController::class, 'index']);
        Route::post('/company/jobs', [CompanyJobController::class, 'store']);
        Route::get('/company/jobs/{job}', [CompanyJobController::class, 'show']);
        Route::put('/company/jobs/{job}', [CompanyJobController::class, 'update']);
        Route::delete('/company/jobs/{job}', [CompanyJobController::class, 'destroy']);
        Route::post('/company/jobs/{job}/submit', [CompanyJobController::class, 'submit']);
    });
});