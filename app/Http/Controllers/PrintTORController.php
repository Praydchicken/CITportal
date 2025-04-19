<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrintTORController extends Controller
{
   public function index($studentNo)
    {
        // Get the student with all related information
        $student = Student::with([
                'user',
                'section',
                'yearLevel',
                'semester',
                'status',
                'studentGrades.curriculum.year_level',
                'studentGrades.curriculum.semester',
            ])
            ->where('student_number', $studentNo)
            ->firstOrFail();

        // Organize all curricula by year level and semester
        $allCurricula = $student->studentGrades
            ->groupBy(function($grade) {
                return 'Year ' . $grade->curriculum->year_level->year_level . ' - ' . 
                    $grade->curriculum->semester->semester_name;
            })
            ->map(function($grades, $groupName) use ($student) {
                return [
                    'group_name' => $groupName,
                    'is_current' => $grades->first()->curriculum->year_level_id == $student->year_level_id &&
                                $grades->first()->curriculum->semester_id == $student->semester_id,
                    'subjects' => $grades->map(function($grade) {
                        return [
                            'curriculum_id' => $grade->curriculum_id,
                            'course_code' => $grade->curriculum->course_code,
                            'subject_name' => $grade->curriculum->subject_name,
                            'lecture_units' => $grade->curriculum->lecture_units,
                            'lab_units' => $grade->curriculum->lab_units,
                            'total_units' => $grade->curriculum->total_units,
                            'raw_grade' => $grade->raw_grade,
                            'gwa_equivalent' => $grade->gwa_equivalent,
                            'grade_remarks' => $grade->grade_remarks,
                            'grade_status' => $grade->grade_status,
                        ];
                    })->sortBy('course_code')
                ];
            })
            ->sortBy(function($group, $key) {
                // Extract year and semester numbers more reliably
                if (preg_match('/Year (\d+) - (\d+)/', $key, $matches)) {
                    return $matches[1] * 10 + $matches[2];
                }
                return 0; // Default value if pattern doesn't match
            });

        // Prepare the student information
        $studentInfo = [
            'student_no' => $student->student_number,
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'last_name' => $student->last_name,
            'email' => $student->user->email,
            'phone_number' => $student->phone_number ?? 'N/A',
            'address' => $student->address ?? 'N/A',
            'section' => $student->section->section ?? 'N/A',
            'year_level' => $student->yearLevel->year_level ?? 'N/A',
            'semester' => $student->semester->semester_name ?? 'N/A',
            'status' => $student->status->status_name ?? 'N/A',
            'is_final_semester' => $student->year_level_id == 4 && $student->semester_id == 2,
            'curricula' => $allCurricula->values(), // Reset array keys
        ];

        // dd($studentInfo);

        return Inertia::render('AdminDashboard/PrintTOR', [
            'title' => 'Print TOR - ' . $student->student_number,
            'studentInfo' => $studentInfo
        ]);
    }
}
