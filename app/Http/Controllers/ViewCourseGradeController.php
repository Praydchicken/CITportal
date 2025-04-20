<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Models\Curriculum;
use App\Models\SchoolYear;
use App\Models\FacultyLoad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ViewCourseGradeController extends Controller
{
   public function index(Request $request) 
    {
        try {
            // Validate request parameters
            $request->validate([
                'student' => 'required|exists:students,id',
                'semester' => 'required|in:1,2'
            ]);

            $user = Auth::user();

            // Get teacher with their faculty loads and related curriculum
            $teacher = Teacher::with(['facultyLoads' => function($query) use ($request) {
                $query->where('semester_id', $request->semester)
                    ->with('curriculum:id,subject_name,course_code,units');
            }])
            ->where('user_id', $user->id)
            ->firstOrFail();

            // Get curriculum IDs from faculty loads
            $assignedCurriculumIds = $teacher->facultyLoads->pluck('curriculum_id')->unique();

            // Get the student with all their grades
            $student = Student::with([
                'section.yearLevel',
                'studentGrades' => function($query) use ($request) {
                    $query->where('semester_id', $request->semester)
                        ->with(['curriculum', 'schoolYear']);
                }
            ])->findOrFail($request->student);

            // Get all school years the student has grades for
            $schoolYears = SchoolYear::whereHas('studentGrades', function($query) use ($student, $request) {
                $query->where('student_id', $student->id)
                    ->where('semester_id', $request->semester);
            })->orderBy('school_year', 'desc')->get();

            // Get current active school year
            $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();

            // Get all curricula for the selected semester and student's year level
            // Filter by teacher's assigned subjects
            $curricula = Curriculum::where('year_level_id', $student->section->yearLevel->id)
                ->where('semester_id', $request->semester)
                ->when($assignedCurriculumIds->isNotEmpty(), function($query) use ($assignedCurriculumIds) {
                    $query->whereIn('id', $assignedCurriculumIds);
                })
                ->get();

            // Prepare courses with historical grades
            $courses = $curricula->map(function ($curriculum) use ($student, $schoolYears) {
                $course = [
                    'id' => $curriculum->id,
                    'subject_name' => $curriculum->subject_name,
                    'course_code' => $curriculum->course_code,
                    'units' => $curriculum->total_units,
                    'grades' => []
                ];

                // Add grades for each school year
                foreach ($schoolYears as $year) {
                    $grade = $student->studentGrades->first(function ($grade) use ($curriculum, $year) {
                        return $grade->curriculum_id == $curriculum->id && 
                            $grade->school_year_id == $year->id;
                    });

                    $course['grades'][$year->id] = $grade ? [
                        'prelim_grade' => $grade->prelim_grade,
                        'midterm_grade' => $grade->midterm_grade,
                        'final_grade' => $grade->final_grade,
                        'raw_grade' => $grade->raw_grade,
                        'gwa_equivalent' => $grade->gwa_equivalent,
                        'grade_remarks' => $grade->grade_remarks,
                        'grade_status' => $grade->grade_status,
                    ] : null;
                }

                return $course;
            });


            return Inertia::render('TeacherDashboard/ViewCourseGrade', [
                'student' => [
                    'id' => $student->id,
                    'student_number' => $student->student_number,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'section' => $student->section->section,
                    'year_level' => $student->section->yearLevel->year_level
                ],
                'semester' => $request->semester,
                'courses' => $courses,
                'schoolYears' => $schoolYears,
                'activeSchoolYear' => $activeSchoolYear,
                'title' => 'View Course Grade',
                // Pass the assigned curriculum IDs to frontend
                'assignedCurriculumIds' => $assignedCurriculumIds,
                'auth' => [
                    'user' => [
                        'name' => "{$teacher->first_name} {$teacher->last_name} | Teacher",
                        'teacher' => $teacher
                    ]
                ],
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error loading course grades: ' . $e->getMessage());
        }
    }

    public function store(Request $request, $studentId) {
        // dd($studentId);

        // Get the authenticated teacher
        $teacher = Teacher::where('user_id', Auth::id())
            ->firstOrFail();

        // Get the student
        $student = Student::where('id', $studentId)->first();

         // Get active school year
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();
        
        if (!$activeSchoolYear) {
            return back()->with('error', 'No active school year found.');
        }

        $request->validate([
            'results' => 'required|array',
            'results.*.subject_name' => 'required|string',
            'results.*.course_code' => 'required|string',
            'results.*.prelim_grade' => 'required|numeric',
            'results.*.midterm_grade' => 'required|numeric',
            'results.*.final_grade' => 'required|numeric',
            'results.*.raw_grade' => 'required|numeric',
            'results.*.converted_grade' => 'required|numeric',
            'results.*.remarks' => 'required|string',
        ]);

        foreach ($request->results as $result) {
            $curriculum = Curriculum::where('course_code', $result['course_code'])->first();

            StudentGrade::create([
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'section_id' => $student->section_id,
                'year_level_id' => $student->year_level_id,
                'semester_id' => $student->semester_id,
                'curriculum_id' => $curriculum->id,
                'prelim_grade' => $result['prelim_grade'],
                'midterm_grade' => $result['midterm_grade'],
                'final_grade' => $result['final_grade'],
                'raw_grade' => $result['raw_grade'],
                'gwa_equivalent' => $result['converted_grade'],
                // Note remove the total_gwa from the studentgrade table then create a table for total average of the student 
                'total_gwa' => $result['converted_grade'],
                'grade_remarks' => $result['remarks'],
                'grade_status' => 'PENDING',
                'school_year_id' => $activeSchoolYear->id,
            ]);
        }

        return back()->with('success', 'Grades submitted, please wait to approved it by the admin');

    }

    public function update(Request $request, $studentId)
    {
        dd($request->all());

        // Validate the request
       $request->validate([
            'editGrades' => 'required|array',
            'editGrades.*.curriculum_id' => 'required|exists:curricula,id',
            'editGrades.*.prelim_grade' => 'required|numeric|min:0|max:100',
            'editGrades.*.midterm_grade' => 'required|numeric|min:0|max:100',
            'editGrades.*.final_grade' => 'required|numeric|min:0|max:100',
            'editGrades.*.raw_grade' => 'required|numeric',
            'editGrades.*.gwa_equivalent' => 'required|numeric',
            'editGrades.*.grade_remarks' => 'required|string|in:PASSED,FAILED',
            'editGrades.*.grade_status' => 'nullable|string',
            'school_year_id' => 'required|exists:school_years,id',
            'semester_id' => 'required|integer',
        ]);



        try {
            DB::beginTransaction();

           foreach ($request->editGrades as $course) {
                StudentGrade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'curriculum_id' => $course['curriculum_id'],
                        'school_year_id' => $request->school_year_id,
                        'semester_id' => $request->semester_id,
                    ],
                    [
                        'prelim_grade' => $course['prelim_grade'],
                        'midterm_grade' => $course['midterm_grade'],
                        'final_grade' => $course['final_grade'],
                        'raw_grade' => $course['raw_grade'],
                        'gwa_equivalent' => $course['gwa_equivalent'],
                        'grade_remarks' => $course['grade_remarks'],
                        'grade_status' => 'Pending',
                    ]
                );
            }


            DB::commit();

            return back()->with('success', 'Grades updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            return back()->with('error', 'Failed to update grades: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {   
        try {
            $studentGrade = StudentGrade::find($id);

            if (!$studentGrade) {
                return back()->withErrors(['error' => 'Student not found']);
            }

            $studentGrade->delete(); // Soft delete (sets deleted_at instead of removing the record)
            return back()->with('success', 'Student deleted successfully');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete student: ' . $e->getMessage()]);
        }
    }
}
