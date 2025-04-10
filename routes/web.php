<?php

use App\Http\Controllers\AdminAnnouncementController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\FacultyLoadController;
use App\Http\Controllers\PostCurriculumConfigController;
use App\Http\Controllers\PostScheduleManagementController;
use App\Http\Controllers\PostSectionManagementController;
use App\Http\Controllers\PostStudentInfoController;
use App\Http\Controllers\SchoolYearController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentGradeController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ClassRoomController;
use App\Models\AdminAnnouncement;
use App\Models\FacultyLoad;
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

    // For admin routes
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/student/info', [PostStudentInfoController::class,'index'])->name('student.info');

    // For admin school year settings
    Route::get('/admin/school/year', [SchoolYearController::class,'index'])->name('admin.school.year');
    Route::post('/admin/school/year', [SchoolYearController::class,'store'])->name('admin.school.year.store');
    Route::post('/admin/school-year/{id}/set-active', [SchoolYearController::class, 'setActive'])->name('admin.school.year.set-active');
    Route::delete('/admin/school-year/{id}/delete', [SchoolYearController::class, 'destroy'])->name('admin.school.year.delete');

    // For managing a teacher
    Route::post('/admin/add/teacher', [TeacherController::class,'store'])->name('admin.add.teacher');
    Route::put('/admin/teachers/{teacher}', [TeacherController::class, 'update'])->name('admin.update.teacher');
    Route::delete('/admin/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('admin.destroy.teacher');

    Route::get('/admin/faulty/load', [FacultyLoadController::class, 'index'])->name('admin.faculty.load');
    Route::post('/admin/faculty/load', [FacultyLoadController::class, 'store']);
    Route::put('/admin/faculty/load/{facultyLoad}', [FacultyLoadController::class, 'update']);
    Route::delete('/admin/faculty/load/{facultyLoad}', [FacultyLoadController::class, 'destroy']);

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

    // For admin classroom settings
    Route::get('/admin/classroom', [ClassRoomController::class,'index'])->name('admin.classroom');
    Route::post('/admin/classroom', [ClassRoomController::class,'store'])->name('admin.classroom.store');
    
    // Teacher management routes
    Route::post('/teacher/add', [TeacherController::class, 'store'])->name('teacher.store');
    Route::put('/teacher/{teacher}/update', [TeacherController::class, 'update'])->name('teacher.update');
    Route::delete('/teacher/{teacher}', [TeacherController::class, 'destroy'])->name('teacher.destroy');

    // New route for showing student details
    Route::get('/students/{id}/details', [PostStudentInfoController::class, 'show'])->name('students.showDetails');
});


   
