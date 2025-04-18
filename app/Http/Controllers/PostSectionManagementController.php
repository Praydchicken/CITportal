<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Section;
use App\Models\YearLevel;
use App\Models\SchoolYear;

class PostSectionManagementController extends Controller
{
    public function index()
    {   
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();
        
        if (!$activeSchoolYear) {
            session()->flash('error', 'No active school year found');
            return back();
        }

        $sections = Section::with('yearLevel')
            ->select('sections.*')
            ->join('year_levels', 'sections.year_level_id', '=', 'year_levels.id')
            ->get()
            ->map(function ($section) use ($activeSchoolYear) {
                return [
                    'id' => $section->id,
                    'section' => $section->section,
                    'year_level' => $section->yearLevel->year_level,
                    'year_level_id' => $section->year_level_id,
                    'min_students' => $section->minimum_number_students,
                    'max_students' => $section->maximum_number_students,
                    'school_year' => $activeSchoolYear->school_year,
                    'school_year_status' => $activeSchoolYear->school_year_status
                ];
            });

        return Inertia::render('AdminDashboard/SectionManagement', [
            'sections' => $sections,
            'title' => 'Sections Management',
            'activeSchoolYear' => $activeSchoolYear,
            'flash' => [
                'success' => session('success'),
                'error' => session('error')
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'section' => 'required|string|max:255',
            'year_level_id' => 'required|exists:year_levels,id',
            'minimum_number_students' => 'required|integer|min:1',
            'maximum_number_students' => 'required|integer|min:1|gt:minimum_number_students'
        ]);

        try {
            $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();
            
            if (!$activeSchoolYear) {
                throw new \Exception('No active school year found');
            }

            Section::create([
                'section' => $request->section,
                'year_level_id' => $request->year_level_id,
                'minimum_number_students' => $request->minimum_number_students,
                'maximum_number_students' => $request->maximum_number_students,
                'school_year_id' => $activeSchoolYear->id
            ]);

            session()->flash('success', 'Section added successfully');
            return redirect()->back()->with('success', 'Section added successfully');
        } catch (\Exception $e) {
            \Log::error('Section creation failed: '.$e->getMessage());
            session()->flash('error', 'Failed to add section: ' . $e->getMessage());
            return back();
        }
    }

    public function update(Request $request, Section $section)
    {
        $request->validate([
            'section' => 'required|string|max:255',
            'year_level_id' => 'required|exists:year_levels,id',
            'minimum_number_students' => 'required|integer|min:1',
            'maximum_number_students' => 'required|integer|min:1|gt:minimum_number_students'
        ]);

        try {
            $section->update([
                'section' => $request->section,
                'year_level_id' => $request->year_level_id,
                'minimum_number_students' => $request->minimum_number_students,
                'maximum_number_students' => $request->maximum_number_students
            ]);

            session()->flash('success', 'Section updated successfully');
            return redirect()->route('admin.section.management');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update section');
            return back();
        }
    }

    public function destroy(Section $section)
    {
        try {
            $section->delete();
            session()->flash('success', 'Section deleted successfully');
            return redirect()->route('admin.section.management');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete section');
            return back();
        }
    }
}
