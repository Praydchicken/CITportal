<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PostScheduleManagementController extends Controller
{
    public function index(){
        return Inertia::render('AdminDashboard/ScheduleManagement', ['title' => 'Schedule Management']);
    }
}
