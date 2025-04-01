<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PostSectionManagementController extends Controller
{
    public function index()
    {   
        return Inertia::render('AdminDashboard/SectionManagement', ['title' => 'Sections Management']);
    }
}
