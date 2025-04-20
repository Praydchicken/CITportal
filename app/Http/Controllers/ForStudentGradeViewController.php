<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ForStudentGradeViewController extends Controller
{
    public function index() {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        $studentGrades = StudentGrade::where('student_id', $student->id)
                                    ->where('grade_status', "APPROVED")
                                    ->get()
                                    ->groupBy(function ($grade) use ($student) {
                                        return $student->year_level_id; // Assuming 'year_level_id' column in students table
                                    })
                                    ->map(function ($grades, $yearLevelId) {
                                        return [
                                            'level' => $yearLevelId,
                                            'grades' => $grades->toArray(),
                                        ];
                                    })
                                    ->values()
                                    ->toArray();
                                    
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