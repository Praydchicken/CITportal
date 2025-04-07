<?php

use App\Http\Controllers\AdminAnnouncementController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacultyLoadController;
use App\Http\Controllers\PostCurriculumConfigController;
use App\Http\Controllers\PostScheduleManagementController;
use App\Http\Controllers\PostSectionManagementController;
use App\Http\Controllers\PostStudentInfoController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentGradeController;
use App\Models\AdminAnnouncement;
use App\Models\FacultyLoad;
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
    Route::post('/admin/add/announcement', [AdminAnnouncementController::class, 'store']);
    Route::post('/admin/announcement', [AdminAnnouncementController::class, 'store'])->name('admin.announcement.store');
    Route::put('/admin/announcement/{adminAnnouncement}', [AdminAnnouncementController::class, 'update'])->name('admin.announcement.update');
    Route::delete('/admin/announcement/{adminAnnouncement}', [AdminAnnouncementController::class, 'destroy'])->name('admin.announcement.destroy');

    // FacultyLoadPage Routes
    Route::get('/admin/faulty/load', [FacultyLoadController::class, 'index'])->name('admin.faculty.load');
    Route::post('/admin/faculty/load', [FacultyLoadController::class, 'store']);
    Route::put('/admin/faculty/load/{facultyLoad}', [FacultyLoadController::class, 'update']);
    Route::delete('/admin/faculty/load/{facultyLoad}', [FacultyLoadController::class, 'destroy']);

    // StudentGrade routes
    Route::get('/admin/student/grade', [StudentGradeController::class, 'index'])->name('admin.student.grade');
    Route::post('/admin/add/student/grade', [StudentGradeController::class, 'store']);
    Route::put('/grade/{id}/update', [StudentGradeController::class, 'update'])->name('grade.update');
    Route::delete('/grade/{id}/delete', [StudentGradeController::class, 'destroy'])->name('grade.delete');

    // Faculty/Admin management routes
    Route::post('/admin/add', [AdminController::class, 'store'])->name('admin.store');
    Route::put('/admin/{admin}/update', [AdminController::class, 'update'])->name('admin.update');
    Route::delete('/admin/{admin}', [AdminController::class, 'destroy'])->name('admin.destroy');

    // For student routes
    Route::get('/student/dashboard', [StudentController::class, 'index'])->name('student.dashboard');
});


   
