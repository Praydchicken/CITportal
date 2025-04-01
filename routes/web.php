<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostSectionManagementController;
use App\Http\Controllers\PostStudentInfoController;
use App\Http\Controllers\StudentController;
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

    // For student routes
    Route::get('/student/dashboard', [StudentController::class, 'index'])->name('student.dashboard');
});

   
