<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Student;
use App\Models\FacultyLoad;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class TeacherAssignedStudentsController extends Controller
{
    public function index()
    {   
        $user = Auth::user();
        // Step 1: Get the authenticated teacher
       $teacher = Teacher::where('user_id', Auth::id())->firstOrFail();

        // Step 2: Get all faculty loads for the teacher with necessary relationships
       $facultyLoads = FacultyLoad::with([
        'curriculum:id,subject_name,course_code',
        'section:id,section',
        'yearLevel:id,year_level',
        'semester:id,semester_name',
        'studentLoads.student:id,first_name,last_name,student_number'
        ])
        ->where('teacher_id', $teacher->id)
        ->get();

        // Step 3: Collect students from student loads
        $students = $facultyLoads->flatMap(function ($load) {
            return $load->studentLoads->map(function ($studentLoad) use ($load) {
                $student = $studentLoad->student;
                return [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'student_number' => $student->student_number,
                    'subject' => $load->curriculum->subject_name,
                    'course_code' => $load->curriculum->course_code,
                    'section' => $load->section->section,
                    'year_level' => $load->yearLevel->year_level,
                    'semester' => $load->semester->semester_name,
                ];
            });
        })->unique('id')->values();

        // Step 4: Return the view with the data
        return Inertia::render('TeacherDashboard/AssignedStudents', [
            'title' => 'Assigned Students',
            'students' => $students,
            'auth' => [
                'user' => [
                    'name' => "{$teacher->first_name} {$teacher->last_name} | Teacher",
                    'teacher' => $teacher
                ]
            ],
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->user->name,
            ]
        ]);
    }
}
