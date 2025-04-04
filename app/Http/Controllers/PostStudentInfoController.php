<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Models\UserType;
use App\Models\YearLevel;
use App\Models\StudentStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PostStudentInfoController extends Controller
{
    public function index(Request $request) {
        $students = Student::with(['section', 'yearLevel', 'user', 'status'])->latest()->get();
        $sections = Section::all(['id', 'section']); // Fetch only necessary fields
        $yearLevels = YearLevel::all(['id', 'year_level']); // Fetch only necessary fields
        $studentStatuses = StudentStatus::all(['id', 'status_name']); // Fetch student statuses

        return Inertia::render('AdminDashboard/StudentInformation', [
            'title' => 'Student Information',
            'students' => $students,
            'sections' => $sections,
            'yearLevels' => $yearLevels,
            'studentStatuses' => $studentStatuses,
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'student_number' => 'required|unique:students',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'section' => 'required|integer', // Ensure section is an ID
            'year_level' => 'required|integer', // Ensure year_level is an ID
            'phone_number' => 'required|string',
            'gender' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'enrollment_date' => 'required|date',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'student_status_id' => 'required|exists:student_statuses,id',
        ]);

        try {
            DB::beginTransaction();

            $userType = UserType::firstOrCreate([
                'user_type' => 'Student',
            ]);

            $user = User::create([
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'user_type_id' => $userType->id,
            ]);

            $student = Student::create([
                'student_number' => $request->student_number,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'section_id' => $request->section,
                'year_level_id' => $request->year_level,
                'phone_number' => $request->phone_number,
                'gender' => $request->gender,
                'address' => $request->address,
                'enrollment_date' => $request->enrollment_date,
                'user_id' => $user->id,
                'student_status_id' => $request->student_status_id,
            ]);

            DB::commit();

            // Load relationships for the response
            $student->load(['section', 'yearLevel', 'user', 'status']);

            return back()->with([
                'message' => 'Successfully added student',
                'student' => $student
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to add student: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, Student $student) {
        $request->validate([
            'student_number' => 'required|unique:students,student_number,' . $student->id,
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'section' => 'required|exists:sections,id',
            'year_level' => 'required|exists:year_levels,id',
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
                'section_id' => $request->section,
                'year_level_id' => $request->year_level,
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

            return redirect()->back()->with('student', $student);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update student: ' . $e->getMessage()]);
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
