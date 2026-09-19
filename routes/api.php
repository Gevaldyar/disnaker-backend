<?php

use App\Http\Controllers\Api\CompanyRegistrationController;
use App\Http\Controllers\Api\CompanyJobController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\JobController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminJobController;
use App\Http\Controllers\Api\AdminCompanyController;
use App\Http\Controllers\Api\AdminNewsController;
use App\Http\Controllers\Api\AdminTrainingController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\TrainingController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminAnnouncementController;
use App\Http\Controllers\Api\AnnouncementController;

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

Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{news}', [NewsController::class, 'show']);

Route::get('/trainings', [TrainingController::class, 'index']);
Route::get('/trainings/{training}', [TrainingController::class, 'show']);

Route::get('/announcements', [AnnouncementController::class, 'index']);
Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show']);

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
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);

    Route::get('/admin/admins', [AdminController::class, 'index']);
    Route::post('/admin/admins', [AdminController::class, 'store']);
    Route::get('/admin/admins/{user}', [AdminController::class, 'show']);
    Route::put('/admin/admins/{user}', [AdminController::class, 'update']);
    Route::delete('/admin/admins/{user}', [AdminController::class, 'destroy']);

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

    Route::get('/admin/news', [AdminNewsController::class, 'index']);
    Route::post('/admin/news', [AdminNewsController::class, 'store']);
    Route::get('/admin/news/{news}', [AdminNewsController::class, 'show']);
    Route::post('/admin/news/{news}', [AdminNewsController::class, 'update']);
    Route::delete('/admin/news/{news}', [AdminNewsController::class, 'destroy']);

    Route::get('/admin/trainings', [AdminTrainingController::class, 'index']);
    Route::post('/admin/trainings', [AdminTrainingController::class, 'store']);
    Route::get('/admin/trainings/{training}', [AdminTrainingController::class, 'show']);
    Route::post('/admin/trainings/{training}', [AdminTrainingController::class, 'update']);
    Route::delete('/admin/trainings/{training}', [AdminTrainingController::class, 'destroy']);

    Route::get('/admin/announcements', [AdminAnnouncementController::class, 'index']);
    Route::post('/admin/announcements', [AdminAnnouncementController::class, 'store']);
    Route::get('/admin/announcements/{announcement}', [AdminAnnouncementController::class, 'show']);
    Route::post('/admin/announcements/{announcement}', [AdminAnnouncementController::class, 'update']);
    Route::delete('/admin/announcements/{announcement}', [AdminAnnouncementController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Company Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:perusahaan')->group(function () {
    Route::get('/company/profile', [CompanyController::class, 'show']);
    Route::put('/company/profile', [CompanyController::class, 'update']);

    Route::get('/company/jobs', [CompanyJobController::class, 'index']);
    Route::post('/company/jobs', [CompanyJobController::class, 'store']);
    Route::get('/company/jobs/{job}', [CompanyJobController::class, 'show']);
    Route::put('/company/jobs/{job}', [CompanyJobController::class, 'update']);
    Route::delete('/company/jobs/{job}', [CompanyJobController::class, 'destroy']);
    Route::post('/company/jobs/{job}/submit', [CompanyJobController::class, 'submit']);

    Route::patch('/company/password', [CompanyController::class, 'changePassword']);
    Route::delete('/company/account', [CompanyController::class, 'destroy']);
    });
});