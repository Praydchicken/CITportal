<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Models\UserType;
use App\Models\YearLevel;
use App\Models\StudentStatus;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PostStudentInfoController extends Controller
{
    public function index()
    {
        $students = Student::with([
            'section',
            'yearLevel',
            'user',
            'status',
            'schoolYear',
            'semester'
        ])->latest()->get();
        $sections = Section::all();
        $yearLevels = YearLevel::all();
        $studentStatuses = StudentStatus::all();
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();
        $semesters = Semester::all();

        return Inertia::render('AdminDashboard/StudentInformation', [
            'title' => 'Student Information',
            'students' => $students,
            'sections' => $sections,
            'yearLevels' => $yearLevels,
            'studentStatuses' => $studentStatuses,
            'activeSchoolYear' => $activeSchoolYear,
            'schoolYears' => $schoolYears,
            'semesters' => $semesters
        ]);
    }

    public function store(Request $request)
    {
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();
        
        if (!$activeSchoolYear) {
            return back()->with('error', 'No active school year found. Please set an active school year before adding students.');
        }

        $validated = $request->validate([
            'student_number' => 'required|string|unique:students,student_number|max:9',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'nullable|string|max:20',
            'gender' => 'required|string|in:Male,Female',
            'address' => 'nullable|string',
            'section_id' => 'required|exists:sections,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'student_status_id' => 'required|exists:student_statuses,id',
            'enrollment_date' => 'required|date',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        // Generate password based on first name and last name
        $rawPassword = strtolower($request->first_name . $request->last_name); // e.g., juandelacruz

        try {
            DB::beginTransaction();

            // Get or create the student user type
            $studentUserType = UserType::where('user_type', 'student')->first();
            
            if (!$studentUserType) {
                $studentUserType = UserType::create(['user_type' => 'student']);
            }

            // Create user account with the auto-generated password
            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($rawPassword),
                'user_type_id' => $studentUserType->id
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'section_id' => $validated['section_id'],
                'year_level_id' => $validated['year_level_id'],
                'student_status_id' => $validated['student_status_id'],
                'school_year_id' => $activeSchoolYear->id,
                'semester_id' => $validated['semester_id'],
                'student_number' => $validated['student_number'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'phone_number' => $validated['phone_number'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'enrollment_date' => $validated['enrollment_date'],
            ]);

            // Get faculty loads based on student's year level and semester
            $facultyLoads = DB::table('faculty_loads')
                ->where('year_level_id', $validated['year_level_id'])
                ->where('semester_id', $validated['semester_id'])
                // Optional: Add school_year_id if necessary
                // ->where('school_year_id', $activeSchoolYear->id)
                ->get();

            // Create student_loads entries
            foreach ($facultyLoads as $load) {
                DB::table('student_loads')->insert([
                    'student_id' => $student->id,
                    'faculty_load_id' => $load->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            // Load relationships including semester
            $student->load(['section', 'yearLevel', 'user', 'status', 'schoolYear', 'semester']); 

            return back()->with([
                'success' => 'Student created successfully',
                'student' => $student
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating student: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Student $student) {
        $validated = $request->validate([
            'student_number' => 'required|unique:students,student_number,' . $student->id,
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'phone_number' => 'nullable|string',
            'gender' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'enrollment_date' => 'required|date',
            'email' => 'required|email|unique:users,email,' . $student->user_id,
            'student_status_id' => 'required|exists:student_statuses,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        try {
            DB::beginTransaction();

            // Update Student model
            $student->update([
                'student_number' => $validated['student_number'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'section_id' => $validated['section_id'],
                'year_level_id' => $validated['year_level_id'],
                'phone_number' => $validated['phone_number'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'enrollment_date' => $validated['enrollment_date'],
                'student_status_id' => $validated['student_status_id'],
                'semester_id' => $validated['semester_id'],
            ]);

            // Update User Email if Changed
            if ($student->user->email !== $validated['email']) {
                $student->user->update([
                    'email' => $validated['email'],
                ]);
            }

            // --- Synchronize Student Loads --- 

            // 1. Remove all existing loads for this student
            DB::table('student_loads')->where('student_id', $student->id)->delete();

            // 2. Check if the student's new status is 'Enrolled'
            $enrolledStatus = StudentStatus::where('status_name', 'Enrolled')->first();
            $isEnrolled = $enrolledStatus && $student->student_status_id == $enrolledStatus->id;

            // 3. If enrolled, find and add new relevant loads
            if ($isEnrolled) {
                $facultyLoadsToAssign = DB::table('faculty_loads')
                    ->where('year_level_id', $student->year_level_id)
                    ->where('semester_id', $student->semester_id)
                    ->where('section_id', $student->section_id)
                    // ->where('school_year_id', $student->school_year_id) // Optional: Add if faculty loads are school year specific
                    ->pluck('id');

                $newStudentLoadData = [];
                foreach ($facultyLoadsToAssign as $facultyLoadId) {
                    $newStudentLoadData[] = [
                        'student_id' => $student->id,
                        'faculty_load_id' => $facultyLoadId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                // Bulk insert new loads if any were found
                if (!empty($newStudentLoadData)) {
                    DB::table('student_loads')->insert($newStudentLoadData);
                }
            }
            // --- End Synchronize Student Loads --- 

            DB::commit();

            // Load relationships for the response
            $student->load(['section', 'yearLevel', 'user', 'status', 'semester']);

            return back()->with([
                'success' => 'Student updated successfully and loads synchronized.',
                'student' => $student
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
             \Log::error('Student Update Error: ' . $e->getMessage()); // Log the error
             \Log::error($e->getTraceAsString());
            return back()->with('error', 'Failed to update student: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {   
        try {
            $student = Student::find($id);

            if (!$student) {
                return back()->withErrors(['error' => 'Student not found']);
            }

            $student->delete(); // Soft delete (sets deleted_at instead of removing the record)
            return back()->with('success', 'Student deleted successfully');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete student: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified student resource with all related details.
     *
     * @param  int  $id The ID of the student.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $student = Student::with([
                'user',
                'section.yearLevel',
                'status',
                'schoolYear',
                'studentLoads.facultyLoad.curriculum',
                'studentLoads.facultyLoad.schedule',
                'studentLoads.facultyLoad.teacher',
                'studentLoads.facultyLoad.semester'
            ])->find($id);


            // dd($student);

            return response()->json($student); // or dd($student) for debugging

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Student not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch student details.'], 500);
        }
    }

}
