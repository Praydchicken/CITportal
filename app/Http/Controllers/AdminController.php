<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Student;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $stats = [
            'enrolled' => Student::whereHas('status', function($query) {
                $query->where('status_name', 'Enrolled');
            })->count(),
            'dropped' => Student::whereHas('status', function($query) {
                $query->where('status_name', 'Dropped');
            })->count(),
            'graduated' => Student::whereHas('status', function($query) {
                $query->where('status_name', 'Graduated');
            })->count(),
        ];

        return Inertia::render('AdminDashboard/Dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Admin $admin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Admin $admin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminRequest $request, Admin $admin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin)
    {
        //
    }
}
