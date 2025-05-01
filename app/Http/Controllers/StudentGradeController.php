<?php

namespace App\Http\Controllers;

use App\Models\StudentGrade;
use App\Models\Student;
use App\Models\Curriculum;
use App\Models\Section;
use App\Models\SchoolYear;
use App\Http\Requests\StoreStudentGradeRequest;
use App\Http\Requests\UpdateStudentGradeRequest;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class StudentGradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Load students with their relationships
        $students = Student::with(['section', 'yearLevel'])->get();
        
        // Load curricula
        $curricula = Curriculum::orderBy('course_code')->get();
        
        // Load all sections with their relationships
        $sections = Section::all();

        // Load school years
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();

        // Load grades with all necessary relationships
        $grades = StudentGrade::with(['student', 'student.section', 'curriculum'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('AdminDashboard/StudentGrade', [
            'title' => 'Student Grade',
            'students' => $students,    
            'curricula' => $curricula,
            'sections' => $sections,
            'activeSchoolYear' => $activeSchoolYear,
            'schoolYears' => $schoolYears,
            'grades' => $grades
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
        try {
            $validator = Validator::make($request->all(), [
                'student_id' => 'required|exists:students,id',
                'curriculum_id' => 'required|exists:curricula,id',
                'section_id' => 'required|exists:sections,id',
                'year_level_id' => 'required|in:1,2,3,4',
                'grade' => 'required|numeric|min:0|max:100',
                'grade_remarks' => 'required|string|max:255',
                'semester_id' => 'required|exists:semesters,id',
                'school_year_id' => 'required|exists:school_years,id'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            // Check if grade already exists for this student, curriculum, year level and semester
            $existingGrade = StudentGrade::where('student_id', $request->student_id)
                ->where('curriculum_id', $request->curriculum_id)
                ->where('year_level_id', $request->year_level_id)
                ->where('semester_id', $request->semester_id)
                ->first();

            if ($existingGrade) {
                return back()->with('error', 'Grade already exists for this student in this subject, semester and year level.');
            }

            // Create new grade
            $grade = StudentGrade::create([
                'student_id' => $request->student_id,
                'curriculum_id' => $request->curriculum_id,
                'section_id' => $request->section_id,
                'year_level_id' => $request->year_level_id,
                'grade' => $request->grade,
                'grade_remarks' => $request->grade_remarks,
                'semester_id' => $request->semester_id,
                'school_year_id' => $request->school_year_id
            ]);

            // Load relationships for the response
            $grade->load(['student', 'curriculum', 'section']);

            return back()->with([
                'success' => 'Grade added successfully.',
                'grade' => $grade
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error adding grade: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StudentGrade $studentGrade)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StudentGrade $studentGrade)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $grade = StudentGrade::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'student_id' => 'required|exists:students,id',
                'curriculum_id' => 'required|exists:curricula,id',
                'section_id' => 'required|exists:sections,id',
                'year_level_id' => 'required|in:1,2,3,4',
                'grade' => 'required|numeric|min:0|max:100',
                'grade_remarks' => 'required|string|max:255',
                'semester_id' => 'required|exists:semesters,id',
                'school_year_id' => 'required|exists:school_years,id'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            // Check if another grade exists for this student, curriculum, year level and semester (excluding current grade)
            $existingGrade = StudentGrade::where('student_id', $request->student_id)
                ->where('curriculum_id', $request->curriculum_id)
                ->where('year_level_id', $request->year_level_id)
                ->where('semester_id', $request->semester_id)
                ->where('id', '!=', $id)
                ->first();

            if ($existingGrade) {
                return back()->with('error', 'Grade already exists for this student in this subject, semester and year level.');
            }

            // Update grade
            $grade->update([
                'student_id' => $request->student_id,
                'curriculum_id' => $request->curriculum_id,
                'section_id' => $request->section_id,
                'year_level_id' => $request->year_level_id,
                'grade' => $request->grade,
                'grade_remarks' => $request->grade_remarks,
                'semester_id' => $request->semester_id,
                'school_year_id' => $request->school_year_id
            ]);

            // Load relationships for the response
            $grade->load(['student', 'curriculum', 'section', 'yearLevel']);

            return back()->with([
                'success' => 'Grade updated successfully.',
                'grade' => $grade
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error updating grade: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $grade = StudentGrade::findOrFail($id);
            $grade->delete();

            return back()->with('success', 'Grade deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete grade: ' . $e->getMessage());
        }
    }


    // For viewing course
    public function viewGrades($studentGradeId) {
        //get the student grade
        $studentGrade = StudentGrade::where('id', $studentGradeId)->first();

        // get the teacher who made a grade
        $teacher = Teacher::select('first_name', 'last_name')
            ->where('id', $studentGrade->teacher_id)
            ->first();

        // get the subject name and course code
        $curriculum =  Curriculum::select('subject_name', 'course_code')
            ->where('id', $studentGrade->curriculum_id)
            ->first();


        return Inertia::render('AdminDashboard/AdminViewGrade', [
            'title' => 'Admin View Grades',
            'studentGrade' => $studentGrade,
            'teacher' => $teacher,
            'curriculum' => $curriculum
        ]);
    }

    // for admin approval grade
    public function approve($studentGradeId) {
        try {
            // Get the student grade
            $studentGrade = StudentGrade::findOrFail($studentGradeId);

            // Update status to APPROVED
            $studentGrade->grade_status = 'APPROVED';
            $studentGrade->save();

            return redirect()->route('admin.student.grade')->with('success', 'Student grade approved successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error updating grade: ' . $e->getMessage());
        }
    }

    public function reject($studentGradeId)
    {
        try {
            $studentGrade = StudentGrade::findOrFail($studentGradeId);
            $studentGrade->grade_status = 'REJECTED';
            $studentGrade->save();

            return redirect()->route('admin.student.grade')->with('success', 'Student grade rejected successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error rejecting grade: ' . $e->getMessage());
        }
    }

}
