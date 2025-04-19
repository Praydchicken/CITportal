<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Curriculum;
use App\Models\YearLevel;
use App\Models\Semester;

class PostCurriculumConfigController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $yearLevel = $request->input('year_level');
        $semester = $request->input('semester');
        
        $query = Curriculum::with(['year_level', 'semester'])
            ->orderBy('year_level_id')
            ->orderBy('semester_id')
            ->orderBy('subject_name');
        
        // Apply filters if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject_name', 'like', "%{$search}%")
                  ->orWhere('course_code', 'like', "%{$search}%");
            });
        }
        
        if ($yearLevel) {
            $query->where('year_level_id', $yearLevel);
        }
        
        if ($semester) {
            $query->where('semester_id', $semester);
        }
        
        // Paginate results
        $curriculums = $query->paginate(10)->withQueryString();
        
        // Check if the request is expecting JSON (for debugging)
        if ($request->expectsJson()) {
            return response()->json($curriculums);
        }
        
        // Get all year levels and semesters for dropdowns
        $yearLevels = YearLevel::all();
        $semesters = Semester::all();

        return Inertia::render('AdminDashboard/CurriculumConfig', [
            'curriculums' => $curriculums,
            'yearLevels' => $yearLevels,
            'semesters' => $semesters,
            'filters' => [
                'search' => $search,
                'year_level' => $yearLevel,
                'semester' => $semester
            ],
            'title' => 'Curriculum Configuration',
            'flash' => [
                'success' => session('success'),
                'error' => session('error')
            ]
        ]);
    }

    protected function calculateTotalUnits($request)
    {
        $lectureUnits = (float)$request->lecture_units;
        $labUnits = (float)$request->lab_units;
        
        return $lectureUnits + $labUnits;
    }

    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'year_level_id' => 'required|exists:year_levels,id',
            'semester_id' => 'required|exists:semesters,id',
            'course_code' => 'required|string|unique:curricula,course_code',
            'subject_name' => 'required|string',
            'description' => 'nullable|string',
            'lecture_units' => 'required|numeric|min:0',
            'lab_units' => 'required|numeric|min:0',
        ]);
        
        // Calculate total units
        $validated['units'] = $this->calculateTotalUnits($request);
        
        // Create the curriculum
        $curriculum = Curriculum::create($validated);
        
        return redirect()->back()->with('success', 'Subject added successfully');
    }

    public function update(Request $request, $id)
    {
        // Find the curriculum
        $curriculum = Curriculum::findOrFail($id);
        
        // Validate the request
        $validated = $request->validate([
            'year_level_id' => 'required|exists:year_levels,id',
            'semester_id' => 'required|exists:semesters,id',
            'course_code' => 'required|string|unique:curricula,course_code,' . $id,
            'subject_name' => 'required|string',
            'description' => 'nullable|string',
            'lecture_units' => 'required|numeric|min:0',
            'lab_units' => 'required|numeric|min:0',
        ]);
        
        // Calculate total units
        $validated['units'] = $this->calculateTotalUnits($request);
        
        // Update the curriculum
        $curriculum->update($validated);
        
        return redirect()->back()->with('success', 'Subject updated successfully');
    }

    public function destroy($id)
    {
        try {
            $subject = Curriculum::findOrFail($id);
            $subject->delete();

            session()->flash('success', 'Subject deleted successfully');
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete subject');
            return redirect()->back();
        }
    }
}
