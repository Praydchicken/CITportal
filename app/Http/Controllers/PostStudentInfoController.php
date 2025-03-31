<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Models\UserType;
use App\Models\YearLevel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PostStudentInfoController extends Controller
{
    public function index(Request $request) {
        $students = Student::with(['section', 'yearLevel', 'user'])->latest()->get();
        $sections = Section::all(['id', 'section']); // Fetch only necessary fields
        $yearLevels = YearLevel::all(['id', 'year_level']); // Fetch only necessary fields

        return Inertia::render('AdminDashboard/StudentInformation', [
            'title' => 'Student Information',
            'students' => $students,
            'sections' => $sections,
            'yearLevels' => $yearLevels,
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
        ]);

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
            'section_id' => $request->section, // Use ID
            'year_level_id' => $request->year_level, // Use ID
            'phone_number' => $request->phone_number,
            'gender' => $request->gender,
            'address' => $request->address,
            'enrollment_date' => $request->enrollment_date,
            'user_id' => $user->id,
        ]);

        return back()->with('message', 'Seccessfully added student');
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
            'status' => 'required|string',
        ]);

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
            'status' => $request->status,
        ]);

        // Update User Email if Changed
        $student->user->update([
            'email' => $request->email,
        ]);

        return redirect()->back()->with('student', $student);
    }


    public function destroy($id)
    {   
        $student = Student::find($id);

        if (!$student) {
            return back()->withErrors(['error' => 'Student not found']);
        }

        $student->delete(); // Soft delete (sets deleted_at instead of removing the record)

        return back()->with('success', 'Student deleted successfully');
    }

}
