<?php

use App\Http\Controllers\AdminAccountingSyncController;
use App\Http\Controllers\TeacherAnnouncementController;
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
use App\Http\Controllers\StudentScheduleController;
use App\Http\Controllers\PrintTORController;
use App\Http\Controllers\StudentBalanceController;
use App\Http\Controllers\TeacherAssignedStudentsController;
use App\Http\Controllers\TeacherAssignedSubjectsController;
use App\Http\Controllers\TeacherClassScheduleController;
use App\Http\Controllers\TeacherGradeManagementController;
use App\Http\Controllers\ViewCourseGradeController;
use App\Http\Controllers\ViewStudentInfoController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsStudent;
use App\Http\Middleware\IsTeacher;
use App\Models\Teacher;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Session;


Route::get('/', function () {
    if (Auth::check()) {
        $role = Auth::user()->userType->user_type;
        return redirect()->route(
            $role === 'Admin' ? 'admin.dashboard' : 'student.dashboard'
        );
    }

    return redirect()->route('login');
});

// This is only for the guest
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create' ])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');


    Route::get('/forgot-password',[ResetPasswordController::class,'requestPassword'])->name('password.request');
    Route::post('/forgot-password', [ResetPasswordController::class, 'sendEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'resetHandler'])->name('password.update');
});

// This is for the auth
Route::middleware(['auth'])->group(function () {
    // Login route
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});

// Admin Routes
Route::middleware(['auth', IsAdmin::class])->group(function () {
     // For admin routes
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/student/info', [PostStudentInfoController::class,'index'])->name('admin.student.info');

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

    Route::post('student/addInfo', [PostStudentInfoController::class, 'store'])->name('student.addInfo');
    Route::delete('student/{id}/delete', [PostStudentInfoController::class, 'destroy']);
    Route::put('student/{student}/update', [PostStudentInfoController::class, 'update']);
    Route::get('/admin/section/management', [PostSectionManagementController::class,'index'])->name('admin.section.management');
    Route::post('/admin/section/add', [PostSectionManagementController::class, 'store'])->name('section.store');
    Route::put('/admin/section/{section}', [PostSectionManagementController::class, 'update'])->name('section.update');
    Route::delete('/admin/section/{section}', [PostSectionManagementController::class, 'destroy'])->name('section.destroy');

    // Schedule Management Routes
    Route::get('/admin/schedule/management', [PostScheduleManagementController::class, 'index'])->name('admin.schedule.management');
    Route::post('/curriculum/subject/add', [PostCurriculumConfigController::class, 'store']);
    Route::put('/curriculum/subject/{id}/update', [PostCurriculumConfigController::class, 'update']);
    Route::delete('/curriculum/subject/{id}/delete', [PostCurriculumConfigController::class, 'destroy']);

    // Curriculum Management Routes
    Route::get('/admin/curriculum/config', [PostCurriculumConfigController::class, 'index'])->name('admin.curriculum.config');

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

    // for admin approval grade
    Route::get('/admin/student/view/grade/{studentGradeId}', [StudentGradeController::class, 'viewGrades'])->name('admin.student.view.grade');
    Route::put('/admin/student/approve/grade/{studentGradeId}', [StudentGradeController::class, 'approve'])->name('admin.student.approve.grade');
    Route::put('/admin/student/reject/grade/{studentGradeId}', [StudentGradeController::class, 'reject'])->name('admin.student.reject.grade');

    // Faculty/Admin management routes
    Route::post('/admin/add', [AdminController::class, 'store'])->name('admin.store');
    Route::put('/admin/{admin}/update', [AdminController::class, 'update'])->name('admin.update');
    Route::delete('/admin/{admin}', [AdminController::class, 'destroy'])->name('admin.destroy');

    // For admin classroom settings
    Route::get('/admin/classroom', [ClassRoomController::class,'index'])->name('admin.classroom');
    Route::post('/admin/classroom', [ClassRoomController::class,'store'])->name('admin.classroom.store');
    
    // Teacher management routes
    Route::post('/teacher/add', [TeacherController::class, 'store'])->name('teacher.store');
    Route::put('/teacher/{teacher}/update', [TeacherController::class, 'update'])->name('teacher.update');
    Route::delete('/teacher/{teacher}', [TeacherController::class, 'destroy'])->name('teacher.destroy');


    Route::post('/admin/sync-financial-records', [AdminAccountingSyncController::class, 'syncFinancialDataFromAPI'])->name('admin.sync.financial-records');

    Route::get('/admin/student-financial-records', function () {
        return Inertia::render('AdminDashboard/AdminAccountingSync');
    })->name('admin.accountingSync');

    Route::get('/students/{id}/details', [PostStudentInfoController::class, 'show'])->name('students.showDetails');
    Route::get('/students/{id}/details', [ViewStudentInfoController::class, 'index'])->name('students.showDetails');

    // Printing a semi TOR
    Route::get('/admin/preview/print/tor/{studentNo}', [PrintTORController::class, 'index'])->name('admin.preview.print.tor');

    // Promote student
    Route::put('/admin/student/promote/{studentNo}', [ViewStudentInfoController::class, 'promote'])->name('admin.student.promote');

    // routes/web.php
    Route::delete('/clear-flash', function() {
        Session::forget(['success', 'error', 'message']);
        return response()->noContent();
    })->name('admin.clear-flash');
});


// Teacher Routes
Route::middleware(['auth', IsTeacher::class])->group(function () {
   // For teacher dashboard and features
    Route::get('/teacher/dashboard', [TeacherController::class, 'index'])->name('teacher.dashboard');
    Route::get('/teacher/class/schedule', [TeacherClassScheduleController::class, 'index'])->name('teacher.class.schedule');
    Route::get('/teacher/assigned/subjects', [TeacherAssignedSubjectsController::class, 'index'])->name('teacher.assigned.subjects');
    Route::get('/teacher/assigned/students', [TeacherAssignedStudentsController::class, 'index'])->name('teacher.assigned.students');

    // Teacher Announce Routes
    Route::get('/teacher/announcement', [TeacherAnnouncementController::class, 'index'])->name('teacher.announcement');
    Route::post('/teacher/announcement', [TeacherAnnouncementController::class, 'store'])->name('teacher.announcement.store');
    Route::put('/teacher/announcement/{teacherAnnouncement}', [TeacherAnnouncementController::class, 'update'])->name('teacher.announcement.update');
    Route::delete('/teacher/announcement/{teacherAnnouncement}', [TeacherAnnouncementController::class, 'destroy'])->name('teacher.announcement.destroy');

    // TeacherGradeManagement
    Route::get('/teacher/grade/management', [TeacherGradeManagementController::class, 'index'])->name('teacher.grade.management');

    Route::get('/teacher/grade/management/view/course/grade', [ViewCourseGradeController::class, 'index'])->name('teacher.grade.management.view.course.grade');
    Route::post('/teacher/grade/management/add/{id}/course/grade', [ViewCourseGradeController::class, 'store'])->name('teacher.grade.management.add.course.grade');
    Route::put('/teacher/grade/management/edit/{id}/course/grade', [ViewCourseGradeController::class, 'update'])->name('teacher.grade.management.edit.course.grade');
    Route::delete('/teacher/grade/management/delete/{id}/course/grade', [ViewCourseGradeController::class, 'destroy'])->name('teacher.grade.management.delete.course.grade');
});

// Student Routes
Route::middleware(['auth', IsStudent::class])->group(function () {
    // For student routes
    Route::get('/student/dashboard', [StudentController::class, 'index'])->name('student.dashboard');
    Route::get('/student/schedule', [StudentScheduleController::class, 'index'])->name('student.schedule');
    Route::get('/student/balances', [StudentBalanceController::class, 'index'])->name('student.balances');

});


   
