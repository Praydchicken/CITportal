<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AdminAnnouncement;
use App\Models\FacultyLoad;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get authenticated user
        $user = Auth::user();

        // Get student record with relationships
        $student = Student::where('user_id', $user->id)
            ->with(['section', 'yearLevel', 'status', 'schoolYear'])  // Ensure to load school year
            ->first();

        // Check if student exists
        if (!$student) {
            // Handle if student record does not exist
            return redirect()->route('login'); // Or appropriate redirect
        }

        // Get relevant announcements using pivot tables
        $announcements = AdminAnnouncement::where(function($query) use ($student) {
            $query->whereDoesntHave('sections')
                ->whereDoesntHave('yearLevels')
                ->orWhereHas('sections', function($q) use ($student) {
                    $q->where('sections.id', $student->section_id);
                })
                ->orWhereHas('yearLevels', function($q) use ($student) {
                    $q->where('year_levels.id', $student->year_level_id);
                });
        })
        ->orderBy('created_at', 'desc')
        ->get();

        // Get student's class schedule using a direct join approach
        $classSchedule = FacultyLoad::select(
            'faculty_loads.*',
            'curricula.subject_name as subject',
            'admins.first_name as faculty_first_name',
            'admins.last_name as faculty_last_name',
            'class_schedules.day',
            'class_schedules.start_time',
            'class_schedules.end_time',
            'class_rooms.room_name',
            'semesters.semester_name'
        )
        ->join('curricula', 'faculty_loads.curriculum_id', '=', 'curricula.id')
        ->join('admins', 'faculty_loads.admin_id', '=', 'admins.id')
        ->join('class_schedules', 'faculty_loads.class_schedule_id', '=', 'class_schedules.id')
        ->join('class_rooms', 'faculty_loads.class_room_id', '=', 'class_rooms.id')
        ->join('semesters', 'faculty_loads.semester_id', '=', 'semesters.id')
        ->join('students', 'students.section_id', '=', 'faculty_loads.section_id') // Join students table
        ->where('students.user_id', $user->id) // Use the authenticated user's ID
        ->where('faculty_loads.section_id', $student->section_id)
        ->where('faculty_loads.year_level_id', $student->year_level_id)
        ->where('students.school_year_id', $student->school_year_id) // Filter by the student's school_year_id
        ->get()
        ->map(function($load) {
            return [
                'subject' => $load->subject,
                'faculty' => $load->faculty_first_name . ' ' . $load->faculty_last_name,
                'day' => $load->day,
                'start_time' => $load->start_time,
                'end_time' => $load->end_time,
                'room' => $load->room_name,
                'semester' => $load->semester_name,
            ];
        });

        // Pass data to the frontend
        return Inertia::render('StudentDashboard/StudentDashboard', [
            'title' => 'Student Dashboard',
            'auth' => [
                'user' => [
                    'name' => "{$student->first_name} {$student->last_name} | Student",
                    'student' => $student
                ]
            ],
            'welcomeMessage' => "Welcome back, {$student->first_name} {$student->last_name}!",
            'announcements' => $announcements,
            'classSchedule' => $classSchedule,
            'debug' => [] // If you want to enable debugging in the future
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        // $credentials = $request->validate([
        //     'student_number' => 'required|unique:students',
        //     'first_name' => 'required|string|max:255',
        //     'middle_name' => 'required|string|max:255',
        //     'last_name' => 'required|string|max:255',
        //     'section' => 'required|string|max:255',
        //     'year_level' => 'required|string|max:255',
        //     'phone_number' => 'required|numeric',
        //     'gender' => 'required|string|max:255',
        //     'address' => 'required|string|max:255',
        //     'enrollment_date' => 'required|string|max:255',
        //     'email' => 'required|email|unique:users,email',
        //     'password' => 'required|min:8',
        // ]);

        // dd($credentials);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        //
    }
}
