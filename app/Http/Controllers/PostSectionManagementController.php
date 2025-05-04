<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Section;
use App\Models\YearLevel;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;

class PostSectionManagementController extends Controller
{
    public function index(Request $request)
    {
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();

        if (!$activeSchoolYear) {
            session()->flash('error', 'No active school year found');
            return back();
        }

        // Default to active school year if none specified
        $selectedSchoolYearId = $request->school_year ?? $activeSchoolYear->id;

        $sections = Section::with(['yearLevel', 'semester'])
            ->where('school_year_id', $selectedSchoolYearId) // Always filter by selected school year
            ->when($request->search, function ($query, $search) {
                return $query->where('section', 'like', "%$search%");
            })
            ->when($request->year_level, function ($query, $yearLevel) {
                return $query->where('year_level_id', $yearLevel);
            })
            ->when($request->semester, function ($query, $semester) {
                return $query->where('semester_id', $semester);
            })
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        // Get supporting data for filters
        $yearLevels = YearLevel::all();
        $semesters = Semester::all();
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();

        return Inertia::render('AdminDashboard/SectionManagement', [
            'sections' => $sections,
            'yearLevels' => $yearLevels,
            'semesters' => $semesters,
            'schoolYears' => $schoolYears,
            'activeSchoolYear' => $activeSchoolYear,
            'filters' => $request->only(['search', 'year_level', 'semester', 'school_year']),
            'title' => 'Sections Management',
            'flash' => [
                'success' => session('success'),
                'error' => session('error')
            ]
        ]);
    }

    public function store(Request $request)
    {
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();

        if (!$activeSchoolYear) {
            return back()->with('error', 'No active school year found. Please set an active school year before adding sections.');
        }

        $validated = $request->validate([
            'section' => 'required|string|max:255|unique:sections,section,NULL,id,school_year_id,' . $activeSchoolYear->id,
            'year_level_id' => 'required|exists:year_levels,id',
            'semester_id' => 'required|exists:semesters,id',
            'minimum_number_students' => 'required|integer|min:1',
            'maximum_number_students' => 'required|integer|min:1|gt:minimum_number_students'
        ]);

        try {
            DB::beginTransaction();

            // Process the section name to remove spaces and standardize format
            $sectionName = strtoupper(str_replace(' ', '', $validated['section']));

            // Create the section with the active school year
            $section = Section::create([
                'section' => $sectionName,
                'year_level_id' => $validated['year_level_id'],
                'semester_id' => $validated['semester_id'],
                'minimum_number_students' => $validated['minimum_number_students'],
                'maximum_number_students' => $validated['maximum_number_students'],
                'school_year_id' => $activeSchoolYear->id
            ]);

            DB::commit();

            // Load relationships
            $section->load(['yearLevel', 'semester']);

            return back()->with([
                'success' => 'Section created successfully',
                'section' => $section
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating section: ' . $e->getMessage());
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
