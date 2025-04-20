<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\TeacherAnnouncement;
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
        $announcements = TeacherAnnouncement::where(function($query) use ($student) {
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