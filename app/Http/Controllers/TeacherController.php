<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminAnnouncement;
use App\Models\TeacherAnnouncement;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        // Get authenticated user
        $user = Auth::user();

        // Get teacher record with relationships
        $teacher = Teacher::where('user_id', $user->id)
            ->with(['user', 'schoolYear', 'facultyLoads' => function($query) {
                $query->with([
                    'curriculum', 
                    'section.students', 
                    'yearLevel', 
                    'schedule', 
                    'semester',
                   
                ]);
            }])
            ->first();
        
        // dd($teacher);
    
        // get the grade student
        $teacherStudentGrades = Teacher::where('user_id', $user->id)->with('studentGrades')->get();
        // dd($teacherStudentGrades);

        // Check if teacher exists
        if (!$teacher) {
            // Handle if teacher record does not exist
            return redirect()->route('login'); // Or appropriate redirect
        }

        // Calculate statistics
        $totalAssignedStudents = 0;
        $totalAssignedSubjects = $teacher->facultyLoads->count();
        $totalApprovedGrades = 0;
        $totalPendingGrades = 0;
        $totalRejectedGrades = 0;

        // Get unique students and count grade statuses
        $totalAssignedStudents = 0;
        $studentIds = [];
        foreach ($teacher->facultyLoads as $load) {
            foreach ($load->studentLoads as $studentLoad) {
                $student = $studentLoad->student;

                // Skip if student is graduated (optional)
                if ($student->student_status_id == 2) continue;

                // Avoid double counting
                if (!in_array($student->id, $studentIds)) {
                    $studentIds[] = $student->id;
                    $totalAssignedStudents++;
                }
            }
        }

        // Count grade statuses
        foreach ($teacherStudentGrades as $teacher) {
            foreach ($teacher->studentGrades as $grade) {
                switch ($grade->grade_status) {
                    case 'Approved':
                        $totalApprovedGrades++;
                        break;
                    case 'Pending':
                        $totalPendingGrades++;
                        break;
                    case 'Rejected':
                        $totalRejectedGrades++;
                        break;
                }
            }
        }
        // dd($studentIds);

        // Get teacher's class schedule
        $classSchedule = $teacher->facultyLoads->map(function($load) {
            return [
                'subject' => $load->curriculum->subject_name,
                'course_code' => $load->curriculum->course_code,
                'section' => $load->section->section,
                'year_level' => $load->yearLevel->year_level,
                'day' => $load->schedule->day,
                'start_time' => $load->schedule->start_time,
                'end_time' => $load->schedule->end_time,
                'semester' => $load->semester->semester_name,
            ];
        });

        return Inertia::render('TeacherDashboard/TeacherDashboard', [
            'title' => 'Teacher Dashboard',
            'auth' => [
                'user' => [
                    'name' => "{$teacher->first_name} {$teacher->last_name} | Teacher",
                    'teacher' => $teacher
                ]
            ],
            'welcomeMessage' => "Welcome back, {$teacher->first_name} {$teacher->last_name}!",
            'classSchedule' => $classSchedule,
            'statistics' => [
                'totalAssignedStudents' => $totalAssignedStudents,
                'totalAssignedSubjects' => $totalAssignedSubjects,
                'totalApprovedGrades' => $totalApprovedGrades,
                'totalPendingGrades' => $totalPendingGrades,
                'totalRejectedGrades' => $totalRejectedGrades,
            ],
            'debug' => [] // If you want to enable debugging in the future
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'school_year_id' => 'required|exists:school_years,id',
        ]);

        // Generate password based on first name and last name
        $rawPassword = strtolower($request->first_name . $request->last_name); // e.g., juandelacruz

        try {
            DB::beginTransaction();

            // Create User first
            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($rawPassword),
                'user_type_id' => UserType::where('user_type', 'Teacher')->first()->id,
            ]);

            // Create Teacher
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'school_year_id' => $validated['school_year_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'phone_number' => $validated['phone_number'] ?? 'N/A',
                'address' => $validated['address'],
            ]);
            DB::commit();

            return back()->with('success', 'Teacher added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Teacher creation failed: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Failed to add teacher.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'email' => 'required|email|unique:users,email,' . $teacher->user_id,
            'password' => 'nullable|string|min:8',
            'school_year_id' => 'required|exists:school_years,id',
        ]);

        try {
            DB::beginTransaction();

            // Update Teacher's info
            $teacher->update([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'phone_number' => $validated['phone_number'] ?? 'N/A',
                'address' => $validated['address'],
                'school_year_id' => $validated['school_year_id'],
            ]);

            // Update User's info
            $userData = [
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $teacher->user->update($userData);

            DB::commit();

            return back()->with('success', 'Teacher updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Teacher update error: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Error updating teacher.']);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        try {
            DB::beginTransaction();
            
            // Get the user_id before deleting the teacher
            $userId = $teacher->user_id;
            
            // Delete the teacher record
            $teacher->delete();
            
            // Delete the associated user record
            if ($userId) {
                User::where('id', $userId)->delete();
            }
            
            DB::commit();
            
            return back()->with('success', 'Teacher deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Teacher Delete Error: ' . $e->getMessage());

            return back()->withErrors(['message' => 'An error occurred while deleting the teacher.']);
        }
    }

}
