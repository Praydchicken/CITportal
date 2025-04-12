<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentScheduleController extends Controller
{
    public function index() {
        return Inertia::render('StudentDashboard/StudentSchedule', ['title' => "Student Schedule"]);
    }
}
