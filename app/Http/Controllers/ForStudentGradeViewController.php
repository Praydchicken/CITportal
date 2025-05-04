<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentGrade;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ForStudentGradeViewController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        $studentGrades = StudentGrade::with(['yearLevel', 'curriculum']) // 👈 Eager load both
            ->where('student_id', $student->id)
            ->where('grade_status', "APPROVED")
            ->get()
            ->map(function ($grade) {
                $gradeArray = $grade->toArray();
                $gradeArray['year_level'] = $grade->yearLevel?->year_level;
                $gradeArray['course_code'] = $grade->curriculum?->course_code;
                return $gradeArray;
            })
            ->groupBy(function ($grade) {
                return $grade['year_level']; // Group by year level name (e.g., "1st Year")
            })
            ->map(function ($grades, $yearLevelName) {
                return [
                    'level' => $yearLevelName,
                    'grades' => $grades,
                ];
            })
            ->values()
            ->toArray();


        // dd($studentGrades);

        return Inertia::render('StudentDashboard/StudentGradeView', [
            'title' => 'Student Grade View',
            'gradesByLevel' => $studentGrades,
            'auth' => [
                'user' => [
                    'name' => "{$student->first_name} {$student->last_name} | Student",
                    'student' => $student
                ]
            ],
        ]);
    }
}
