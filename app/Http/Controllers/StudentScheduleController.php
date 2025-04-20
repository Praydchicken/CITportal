<?php

namespace App\Http\Controllers;
namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Semester;
use App\Models\FacultyLoad;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Inertia\Inertia;

class StudentScheduleController extends Controller
{
    /**
     * Display the student's class schedule.
     */
    public function index()
    {
        // Get authenticated student
        $student = Student::where('user_id', Auth::id())->first();

        // Check if student exists
        if (!$student) {
            return redirect()->route('login');
        }

        // Get the student's section_id and semester_id
        $section_id = $student->section_id;
        $semester_id = $student->semester_id;

        // Retrieve the faculty loads for the student's section and semester
        $facultyLoad = FacultyLoad::with([
            'curriculum',
            'section',
            'classSchedule',
            'semester',
            'teacher'
        ])
        ->where('section_id', $section_id)  // Filter by section
        ->where('semester_id', $semester_id)  // Filter by semester
        ->get();

        // Check if faculty load exists
        // if ($facultyLoad->isEmpty()) {
        //     return redirect()->route('student.dashboard')->with('error', 'No class schedule found for this student.');
        // }

        // Retrieve class schedules based on faculty load
        $classSchedule = $facultyLoad->map(function($load) {
            return [
                'subject' => $load->curriculum->subject_name,
                'course_code' => $load->curriculum->course_code,
                'section' => $load->section->section_name,
                'day' => $load->classSchedule->day,
                'start_time' => Carbon::parse($load->classSchedule->start_time)->format('h:i A'),  // Format to 12-hour AM/PM
                'end_time' => Carbon::parse($load->classSchedule->end_time)->format('h:i A'),  // Format to 12-hour AM/PM
                'teacher_name' => $load->teacher ? $load->teacher->first_name . ' ' . $load->teacher->last_name : 'No Teacher Assigned', // Ensure teacher exists
                'semester' => $load->semester->semester_name,
            ];
        });


        // Return data to the frontend (Inertia)
        return Inertia::render('StudentDashboard/StudentSchedule', [
            'title' => 'My Class Schedule',
            'classSchedule' => $classSchedule,
            'auth' => [
                'user' => [
                    'name' => "{$student->first_name} {$student->last_name} | Student",
                    'student' => $student
                ]
            ]
        ]);
    }
}
