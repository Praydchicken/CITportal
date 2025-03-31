<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;

use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('StudentDashboard', ['title' => 'Student Dashboard']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        // $credentials = $request->validate([
        //     'student_number' => 'required|unique:students',
        //     'first_name' => 'required|string|max:255',
        //     'middle_name' => 'required|string|max:255',
        //     'last_name' => 'required|string|max:255',
        //     'section' => 'required|string|max:255',
        //     'year_level' => 'required|string|max:255',
        //     'phone_number' => 'required|numeric',
        //     'gender' => 'required|string|max:255',
        //     'address' => 'required|string|max:255',
        //     'enrollment_date' => 'required|string|max:255',
        //     'email' => 'required|email|unique:users,email',
        //     'password' => 'required|min:8',
        // ]);

        // dd($credentials);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        //
    }
}
