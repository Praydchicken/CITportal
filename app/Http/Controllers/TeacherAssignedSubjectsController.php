<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Teacher;
use App\Models\FacultyLoad;
use Illuminate\Support\Facades\Auth;

class TeacherAssignedSubjectsController extends Controller
{
    public function index()
    {   
        $user = Auth::user();
        // Get the authenticated user's teacher record
        $teacher = Teacher::where('user_id', Auth::id())
            ->firstOrFail();

        // Get the teacher's faculty loads with related data
        $subjects = FacultyLoad::with([
            'curriculum:id,subject_name,course_code',
            'section:id,section',
            'yearLevel:id,year_level',
            'semester:id,semester_name',
        ])
        ->where('teacher_id', $teacher->id)
        ->get()
        ->map(function ($load) {
            return [
                'id' => $load->id,
                'subject_name' => $load->curriculum->subject_name,
                'course_code' => $load->curriculum->course_code,
                'section' => $load->section->section,
                'year_level' => $load->yearLevel->year_level,
                'semester' => $load->semester->semester_name,
                // Add any additional fields you need
            ];
        });

        return Inertia::render('TeacherDashboard/AssignedSubjects', [
            'title' => 'Assigned Subjects',
            'subjects' => $subjects,
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->user->name,
            ],
             'auth' => [
                'user' => [
                    'name' => "{$teacher->first_name} {$teacher->last_name} | Teacher",
                    'teacher' => $teacher
                ]
            ]
        ]);
    }
}
