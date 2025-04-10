<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Http\Requests\StoreSchoolYearRequest;
use App\Http\Requests\UpdateSchoolYearRequest;
use Inertia\Inertia;
use Illuminate\Http\Request;

class SchoolYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() 
    {
        $schoolYears = SchoolYear::orderBy('id', 'asc')->get();

        return Inertia::render('AdminDashboard/SchoolYear', [
            'title' => 'School Year',
            'schoolYears' => $schoolYears,
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
    public function store(StoreSchoolYearRequest $request)
    {
        // If the new school year is set as active, update any existing active school year to inactive
        if ($request->school_year_status === 'Active') {
            SchoolYear::where('school_year_status', 'Active')->update(['school_year_status' => 'Inactive']);
        }

        $schoolYear = SchoolYear::create([
            'school_year' => $request->school_year,
            'school_year_status' => $request->school_year_status
        ]);

        return redirect()->back()->with('success', 'School year created successfully.');
    }

    /**
     * Set the specified school year as active.
     */
    public function setActive(SchoolYear $id)
    {
        // Update all school years to inactive first
        SchoolYear::where('school_year_status', 'Active')->update(['school_year_status' => 'Inactive']);
        
        // Set the selected school year to active
        $id->update(['school_year_status' => 'Active']);
        
        return redirect()->back()->with('success', 'School year set as active successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolYear $schoolYear)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SchoolYear $schoolYear)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSchoolYearRequest $request, SchoolYear $schoolYear)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Find the school year by ID, or fail with a 404 response if not found
            $schoolYear = SchoolYear::findOrFail($id);

            // Delete the school year
            $schoolYear->delete();

            // Redirect back with a success message
            return redirect()->back()->with('success', 'School year deleted successfully.');

        } catch (\Exception $e) {
            // In case of failure, redirect back with an error message
            return redirect()->back()->with('error', 'Failed to delete the school year.');
        }
    }

}
