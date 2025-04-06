<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Models\UserType;
use App\Models\YearLevel;
use App\Models\StudentStatus;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PostStudentInfoController extends Controller
{
    public function index()
    {
        $students = Student::with(['section', 'yearLevel', 'user', 'status', 'schoolYear'])->latest()->get();
        $sections = Section::all();
        $yearLevels = YearLevel::all();
        $studentStatuses = StudentStatus::all();
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();

        return Inertia::render('AdminDashboard/StudentInformation', [
            'title' => 'Student Information',
            'students' => $students,
            'sections' => $sections,
            'yearLevels' => $yearLevels,
            'studentStatuses' => $studentStatuses,
            'activeSchoolYear' => $activeSchoolYear,
            'schoolYears' => $schoolYears
        ]);
    }

    public function store(Request $request)
    {
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();
        
        if (!$activeSchoolYear) {
            return back()->with('error', 'No active school year found. Please set an active school year before adding students.');
        }

        $validated = $request->validate([
            'student_number' => 'required|string|unique:students,student_number',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20',
            'gender' => 'required|string|in:Male,Female',
            'address' => 'required|string',
            'section_id' => 'required|exists:sections,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'student_status_id' => 'required|exists:student_statuses,id',
            'enrollment_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            // Get or create the student user type
            $studentUserType = UserType::where('user_type', 'student')->first();
            
            if (!$studentUserType) {
                $studentUserType = UserType::create(['user_type' => 'student']);
            }

            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make('password123'),
                'user_type_id' => $studentUserType->id
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'section_id' => $validated['section_id'],
                'year_level_id' => $validated['year_level_id'],
                'student_status_id' => $validated['student_status_id'],
                'school_year_id' => $activeSchoolYear->id,
                'student_number' => $validated['student_number'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'phone_number' => $validated['phone_number'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'enrollment_date' => $validated['enrollment_date'],
            ]);

            DB::commit();

            $student->load(['section', 'yearLevel', 'user', 'status', 'schoolYear']);

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
        $request->validate([
            'student_number' => 'required|unique:students,student_number,' . $student->id,
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'phone_number' => 'required|string',
            'gender' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'enrollment_date' => 'required|date',
            'email' => 'required|email|unique:users,email,' . $student->user_id,
            'student_status_id' => 'required|exists:student_statuses,id',
        ]);

        try {
            DB::beginTransaction();

            $student->update([
                'student_number' => $request->student_number,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'section_id' => $request->section_id,
                'year_level_id' => $request->year_level_id,
                'phone_number' => $request->phone_number,
                'gender' => $request->gender,
                'address' => $request->address,
                'enrollment_date' => $request->enrollment_date,
                'student_status_id' => $request->student_status_id,
            ]);

            // Update User Email if Changed
            $student->user->update([
                'email' => $request->email,
            ]);

            DB::commit();

            // Load relationships for the response
            $student->load(['section', 'yearLevel', 'user', 'status']);

            return back()->with([
                'success' => 'Student updated successfully',
                'student' => $student
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
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
}
