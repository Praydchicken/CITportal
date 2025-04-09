<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// This is only for the guest
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'create' ])->name('login');
    Route::post('/', [AuthController::class, 'store' ])->name('login');

    Route::get('/forgot-password',[ResetPasswordController::class,'requestPassword'])->name('password.request');
    Route::post('/forgot-password', [ResetPasswordController::class, 'sendEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'resetHandler'])->name('password.update');
});

// This is for the auth
Route::middleware('auth')->group(function () {
    // Login route
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    Route::get('/student/dashboard', [StudentController::class, 'index'])->name('student.dashboard');
});
