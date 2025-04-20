<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentAnnouncementViewController extends Controller
{
    public function index() {
        return Inertia::render('StudentDashboard/StudentAnnouncementView');
    }
}
