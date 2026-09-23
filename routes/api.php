<?php

use App\Http\Controllers\Api\AdminAnnouncementController;
use App\Http\Controllers\Api\AdminCompanyController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminJobController;
use App\Http\Controllers\Api\AdminJobSeekerController;
use App\Http\Controllers\Api\AdminNewsController;
use App\Http\Controllers\Api\AdminPageController;
use App\Http\Controllers\Api\AdminServiceController;
use App\Http\Controllers\Api\AdminTrainingController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CompanyDashboardController;
use App\Http\Controllers\Api\CompanyJobController;
use App\Http\Controllers\Api\CompanyRegistrationController;
use App\Http\Controllers\Api\EmploymentStatisticController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\JobSeekerProfileController;
use App\Http\Controllers\Api\JobSeekerSkillController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TrainingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Test
|--------------------------------------------------------------------------
*/

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API Disnaker berhasil berjalan',
    ]);
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/{job}', [JobController::class, 'show']);

Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{news}', [NewsController::class, 'show']);

Route::get('/trainings', [TrainingController::class, 'index']);
Route::get('/trainings/{training}', [TrainingController::class, 'show']);

Route::get('/announcements', [AnnouncementController::class, 'index']);
Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show']);

Route::get('/employment-statistics', [EmploymentStatisticController::class, 'index']);

Route::get('/pages', [PageController::class, 'index']);
Route::get('/pages/{slug}', [PageController::class, 'show']);

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{slug}', [ServiceController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

Route::post(
    '/company/register',
    [CompanyRegistrationController::class, 'register']
);

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

/*
|--------------------------------------------------------------------------
| Job Seeker Routes
|--------------------------------------------------------------------------
*/

Route::prefix('job-seeker')
    ->middleware(['auth:sanctum', 'role:pencari_kerja'])
    ->group(function () {

        // Profile
        Route::get('/profile', [
            JobSeekerProfileController::class,
            'show'
        ]);

        Route::put('/profile', [
            JobSeekerProfileController::class,
            'update'
        ]);

        Route::delete('/profile', [
            JobSeekerProfileController::class,
            'destroy'
        ]);

        // Skills
        Route::get('/skills', [
            JobSeekerSkillController::class,
            'index'
        ]);

        Route::post('/skills', [
            JobSeekerSkillController::class,
            'store'
        ]);

        Route::put('/skills/{skill}', [
            JobSeekerSkillController::class,
            'update'
        ]);

        Route::delete('/skills/{skill}', [
            JobSeekerSkillController::class,
            'destroy'
        ]);
    });

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [
            AdminDashboardController::class,
            'index'
        ]);

        // Admin Management
        Route::get('/admins', [
            AdminController::class,
            'index'
        ]);

        Route::post('/admins', [
            AdminController::class,
            'store'
        ]);

        Route::get('/admins/{user}', [
            AdminController::class,
            'show'
        ]);

        Route::put('/admins/{user}', [
            AdminController::class,
            'update'
        ]);

        Route::delete('/admins/{user}', [
            AdminController::class,
            'destroy'
        ]);

        // Company Management
        Route::get('/companies', [
            AdminCompanyController::class,
            'index'
        ]);

        Route::get('/companies/pending', [
            AdminCompanyController::class,
            'pending'
        ]);

        Route::get('/companies/{company}', [
            AdminCompanyController::class,
            'show'
        ]);

        Route::patch('/companies/{company}/approve', [
            AdminCompanyController::class,
            'approve'
        ]);

        Route::patch('/companies/{company}/reject', [
            AdminCompanyController::class,
            'reject'
        ]);

        Route::patch('/companies/{company}/suspend', [
            AdminCompanyController::class,
            'suspend'
        ]);

        // Job Management
        Route::get('/jobs/pending', [
            AdminJobController::class,
            'pending'
        ]);

        Route::get('/jobs', [
            AdminJobController::class,
            'index'
        ]);

        Route::patch('/jobs/{job}/approve', [
            AdminJobController::class,
            'approve'
        ]);

        Route::patch('/jobs/{job}/reject', [
            AdminJobController::class,
            'reject'
        ]);

        Route::delete('/jobs/{job}', [
            AdminJobController::class,
            'destroy'
        ]);

        // Job Seeker Management
        Route::get('/job-seekers', [
            AdminJobSeekerController::class,
            'index'
        ]);

        Route::post('/job-seekers', [
            AdminJobSeekerController::class,
            'store'
        ]);

        Route::get('/job-seekers/{jobSeeker}', [
            AdminJobSeekerController::class,
            'show'
        ]);

        Route::put('/job-seekers/{jobSeeker}', [
            AdminJobSeekerController::class,
            'update'
        ]);

        Route::delete('/job-seekers/{jobSeeker}', [
            AdminJobSeekerController::class,
            'destroy'
        ]);

        Route::patch('/job-seekers/{jobSeeker}/verify', [
            AdminJobSeekerController::class,
            'verify'
        ]);

        Route::patch('/job-seekers/{jobSeeker}/reject', [
            AdminJobSeekerController::class,
            'reject'
        ]);

        // News Management
        Route::get('/news', [
            AdminNewsController::class,
            'index'
        ]);

        Route::post('/news', [
            AdminNewsController::class,
            'store'
        ]);

        Route::get('/news/{news}', [
            AdminNewsController::class,
            'show'
        ]);

        Route::post('/news/{news}', [
            AdminNewsController::class,
            'update'
        ]);

        Route::delete('/news/{news}', [
            AdminNewsController::class,
            'destroy'
        ]);

        // Announcement Management
        Route::get('/announcements', [
            AdminAnnouncementController::class,
            'index'
        ]);

        Route::post('/announcements', [
            AdminAnnouncementController::class,
            'store'
        ]);

        Route::get('/announcements/{announcement}', [
            AdminAnnouncementController::class,
            'show'
        ]);

        Route::post('/announcements/{announcement}', [
            AdminAnnouncementController::class,
            'update'
        ]);

        Route::delete('/announcements/{announcement}', [
            AdminAnnouncementController::class,
            'destroy'
        ]);

        // Training Management
        Route::get('/trainings', [
            AdminTrainingController::class,
            'index'
        ]);

        Route::post('/trainings', [
            AdminTrainingController::class,
            'store'
        ]);

        Route::get('/trainings/{training}', [
            AdminTrainingController::class,
            'show'
        ]);

        Route::post('/trainings/{training}', [
            AdminTrainingController::class,
            'update'
        ]);

        Route::delete('/trainings/{training}', [
            AdminTrainingController::class,
            'destroy'
        ]);

        // Page Management
        Route::get('/pages', [
            AdminPageController::class,
            'index'
        ]);

        Route::post('/pages', [
            AdminPageController::class,
            'store'
        ]);

        Route::get('/pages/{page}', [
            AdminPageController::class,
            'show'
        ]);

        Route::post('/pages/{page}', [
            AdminPageController::class,
            'update'
        ]);

        Route::delete('/pages/{page}', [
            AdminPageController::class,
            'destroy'
        ]);

        // Service Management
        Route::get('/services', [
            AdminServiceController::class,
            'index'
        ]);

        Route::post('/services', [
            AdminServiceController::class,
            'store'
        ]);

        Route::get('/services/{service}', [
            AdminServiceController::class,
            'show'
        ]);

        Route::post('/services/{service}', [
            AdminServiceController::class,
            'update'
        ]);

        Route::delete('/services/{service}', [
            AdminServiceController::class,
            'destroy'
        ]);
    });

/*
|--------------------------------------------------------------------------
| Company Routes
|--------------------------------------------------------------------------
*/

Route::prefix('company')
    ->middleware(['auth:sanctum', 'role:perusahaan'])
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [
            CompanyDashboardController::class,
            'index'
        ]);

        // Profile
        Route::get('/profile', [
            CompanyController::class,
            'show'
        ]);

        Route::put('/profile', [
            CompanyController::class,
            'update'
        ]);

        // Jobs
        Route::get('/jobs', [
            CompanyJobController::class,
            'index'
        ]);

        Route::post('/jobs', [
            CompanyJobController::class,
            'store'
        ]);

        Route::get('/jobs/{job}', [
            CompanyJobController::class,
            'show'
        ]);

        Route::put('/jobs/{job}', [
            CompanyJobController::class,
            'update'
        ]);

        Route::delete('/jobs/{job}', [
            CompanyJobController::class,
            'destroy'
        ]);

        Route::post('/jobs/{job}/submit', [
            CompanyJobController::class,
            'submit'
        ]);

        // Account
        Route::patch('/password', [
            CompanyController::class,
            'changePassword'
        ]);

        Route::delete('/account', [
            CompanyController::class,
            'destroy'
        ]);
    });