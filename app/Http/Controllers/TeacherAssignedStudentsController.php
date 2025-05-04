<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Student;
use App\Models\FacultyLoad;
use App\Models\SchoolYear;
use App\Models\Section;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

/**
 * TODO: Make this work where it needs to show the assigned students base on their school year and section 
 */
class TeacherAssignedStudentsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Step 1: Get the authenticated teacher
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

        // Step 2: Get all faculty loads with relationships
        $facultyLoads = FacultyLoad::with([
            'curriculum:id,subject_name,course_code',
            'section:id,section',
            'yearLevel:id,year_level',
            'semester:id,semester_name',
            'studentLoads.student:id,first_name,last_name,student_number'
        ])
            ->where('teacher_id', $teacher->id)
            ->get();

        // Step 3: Collect all student info per faculty load
        $students = $facultyLoads->flatMap(function ($facultyLoad) {
            // If no students, still return subject info with empty values
            if ($facultyLoad->studentLoads->isEmpty()) {
                return [[
                    'id' => null,
                    'name' => null,
                    'student_number' => null,
                    'subject' => $facultyLoad->curriculum->subject_name,
                    'course_code' => $facultyLoad->curriculum->course_code,
                    'section' => $facultyLoad->section->section,
                    'year_level' => $facultyLoad->yearLevel->year_level,
                    'semester' => $facultyLoad->semester->semester_name,
                ]];
            }

            // Otherwise, map each student
            return $facultyLoad->studentLoads->map(function ($studentLoad) use ($facultyLoad) {
                $student = $studentLoad->student;

                return [
                    'id' => $student->id,
                    'name' => "{$student->first_name} {$student->last_name}",
                    'student_number' => $student->student_number,
                    'subject' => $facultyLoad->curriculum->subject_name,
                    'course_code' => $facultyLoad->curriculum->course_code,
                    'section' => $facultyLoad->section->section,
                    'year_level' => $facultyLoad->yearLevel->year_level,
                    'semester' => $facultyLoad->semester->semester_name,
                ];
            });
        })->values(); // no need for unique if showing duplicates by subject

        // dd($students); // for debugging

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
