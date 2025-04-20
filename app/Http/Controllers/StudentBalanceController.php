<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentBalanceController extends Controller
{
    public function index(Request $request)
    {
        $student = Student::where('user_id', Auth::id())->first();
        // Validate the request data
    return Inertia::render('StudentDashboard/StudentBalance',[
           'title' => 'My Balance',
           'auth' => [
            'user' => [
                'name' => "{$student->first_name} {$student->last_name} | Student",
                'student' => $student
            ]
        ]
        ]);
    }
}
