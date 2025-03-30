<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// This is only for the guest
Route::middleware('guest')->group(function () {

    Route::get('/', function () {
        return Inertia::render('Login');
    });

    Route::get('/login', [AuthController::class, 'create' ])->name('login');
    Route::post('/login', [AuthController::class, 'store' ])->name('login');

});

// This is for the auth
Route::middleware('auth')->group(function () {
    // Login route
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    Route::get('/student/dashboard', [StudentController::class, 'index'])->name('student.dashboard');
});
