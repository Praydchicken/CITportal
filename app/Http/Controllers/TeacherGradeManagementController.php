<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\FacultyLoad;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\YearLevel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherGradeManagementController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Step 1: Get the authenticated teacher
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        try {
            // Get the authenticated teacher
            $teacher = Teacher::where('user_id', Auth::id())
                ->firstOrFail();

            // Get active school year
            $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();
            
            if (!$activeSchoolYear) {
                return back()->with('error', 'No active school year found. Please contact the administrator.');
            }

            // Get faculty loads for the teacher
            $facultyLoads = FacultyLoad::where('teacher_id', $teacher->id)
                ->with(['section.yearLevel', 'schoolYear'])
                ->get();

            // Get unique sections and year levels from faculty loads
            $sections = $facultyLoads->pluck('section')->unique()->values();
            $yearLevels = $facultyLoads->pluck('section.yearLevel')->unique()->values();
            $schoolYears = $facultyLoads->pluck('schoolYear')->unique()->values();

            // Get all students assigned to this teacher's classes
            $students = DB::table('students')
                ->join('student_loads', 'students.id', '=', 'student_loads.student_id')
                ->join('faculty_loads', 'student_loads.faculty_load_id', '=', 'faculty_loads.id')
                ->join('sections', 'faculty_loads.section_id', '=', 'sections.id')
                ->join('year_levels', 'sections.year_level_id', '=', 'year_levels.id')
                ->join('school_years', 'faculty_loads.school_year_id', '=', 'school_years.id')
                ->where('faculty_loads.teacher_id', $teacher->id)
                ->select([
                    'students.id',
                    'students.student_number',
                    'students.first_name',
                    'students.last_name',
                    'sections.section',
                    'year_levels.year_level',
                    'school_years.id as school_year_id',
                    'school_years.school_year'
                ])
                ->distinct()
                ->get();

            return Inertia::render('TeacherDashboard/TeacherGradeManagement', [
                'students' => $students,
                'sections' => $sections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'section' => $section->section
                    ];
                }),
                'yearLevels' => $yearLevels->map(function ($yearLevel) {
                    return [
                        'id' => $yearLevel->id,
                        'year_level' => $yearLevel->year_level
                    ];
                }),
                'schoolYears' => $schoolYears->map(function ($schoolYear) {
                    return [
                        'id' => $schoolYear->id,
                        'school_year' => $schoolYear->school_year
                    ];
                }),
                'activeSchoolYear' => $activeSchoolYear,
                'title' => 'Grade Management',
                'auth' => [
                    'user' => [
                        'name' => "{$teacher->first_name} {$teacher->last_name} | Teacher",
                        'teacher' => $teacher
                    ]
                ],
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error loading grade management: ' . $e->getMessage());
        }
    }
}
