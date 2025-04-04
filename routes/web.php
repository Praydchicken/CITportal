<?php

use App\Http\Controllers\AdminAnnouncementController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostCurriculumConfigController;
use App\Http\Controllers\PostScheduleManagementController;
use App\Http\Controllers\PostSectionManagementController;
use App\Http\Controllers\PostStudentInfoController;
use App\Http\Controllers\StudentController;
use App\Models\AdminAnnouncement;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// This is only for the guest
Route::middleware('guest')->group(function () {

    Route::get('/', function () {
        return Inertia::render('Login');
    });

    Route::get('/login', [AuthController::class, 'index' ])->name('login');
    Route::post('/login', [AuthController::class, 'login' ])->name('login');

});

// This is for the auth
Route::middleware('auth')->group(function () {
    // Login route
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    // For admin routes
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/student/info', [PostStudentInfoController::class,'index'])->name('student.info');
    Route::post('/student/addInfo', [PostStudentInfoController::class, 'store'])->name('student.addInfo');
    Route::delete('/student/{id}/delete', [PostStudentInfoController::class, 'destroy']);
    Route::put('/student/{student}/update', [PostStudentInfoController::class, 'update']);
    Route::get('/admin/section/management', [PostSectionManagementController::class,'index'])->name('admin.section.management');
    Route::post('/section/add', [PostSectionManagementController::class, 'store'])->name('section.store');
    Route::put('/section/{section}', [PostSectionManagementController::class, 'update'])->name('section.update');
    Route::delete('/section/{section}', [PostSectionManagementController::class, 'destroy'])->name('section.destroy');

    // Schedule Management Routes
    Route::get('/admin/schedule/management', [PostScheduleManagementController::class, 'index'])->name('admin.schedule.management');
    Route::post('/curriculum/subject/add', [PostCurriculumConfigController::class, 'store']);
    Route::put('/curriculum/subject/{id}/update', [PostCurriculumConfigController::class, 'update']);
    Route::delete('/curriculum/subject/{id}/delete', [PostCurriculumConfigController::class, 'destroy']);

    // Curriculum Management Routes
    Route::get('/admin/curriculum/config', [PostCurriculumConfigController::class, 'index'])->name('admin.curriculum.config');

    // Admin Announce Routes
    Route::get('/admin/announcement', [AdminAnnouncementController::class, 'index'])->name('admin.announcement');

    // For student routes
    Route::get('/student/dashboard', [StudentController::class, 'index'])->name('student.dashboard');
});

   
