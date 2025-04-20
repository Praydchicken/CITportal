<?php

namespace App\Http\Controllers;

use App\Models\FinancialRecord;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentProfileController extends Controller
{
    public function index(){

        $user = Auth::user();
        
        $student = Student::with([
            'section',
            'yearLevel',
            'status',
            'semester',
            'user'
        ])->where('user_id', $user->id)->firstOrFail();
        
        $email = $student->user->email;

        $studentInfo = [
            'student_no' => $student->student_number,
            'email' => $email,
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'last_name' => $student->last_name,
            'phone_number' => $student->phone_number ?? 'N/A',
            'address' => $student->address ?? 'N/A',
            'section'  => $student->section->section ?? 'N/A',
            'year_level' => $student->yearLevel->year_level ?? 'N/A',
            'semester' => $student->semester->semester_name,
            'status' => $student->status->status_name,
            // 'curricula' => $curriculaWithGrades, // now includes all curricula with grades!
            'is_final_semester' => $student->year_level_id == 4 && $student->semester_id == 2
        ];
        // dd($student);

        $studentFinancial = FinancialRecord::where('student_number', $student->student_number)->get();
        // dd($studentFinancial);

        return Inertia::render('StudentDashboard/StudentProfile',[
            'title' => "Profile Information",
            'studentInfo' => $studentInfo,
            'studentFinancial' => $studentFinancial,
            'auth' => [
                'user' => [
                    'name' => "{$student->first_name} {$student->last_name} | Student",
                    'student' => $student
                ]
            ],
        ]);
    }
}
