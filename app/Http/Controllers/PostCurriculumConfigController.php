<?php

namespace App\Http\Controllers;

use App\Models\Curriculum;
use App\Models\YearLevel;
use App\Models\Semester;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostCurriculumConfigController extends Controller
{
    public function index()
    {
        $subjects = Curriculum::with(['yearLevel', 'semester'])->latest()->get();
        $yearLevels = YearLevel::all();
        $semesters = Semester::all();

        return Inertia::render('AdminDashboard/CurriculumConfig', [
            'subjects' => $subjects,
            'yearLevels' => $yearLevels,
            'semesters' => $semesters,
            'title' => 'Curriculum Configuration',
            'flash' => [
                'success' => session('success'),
                'error' => session('error')
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'year_level_id' => 'required|exists:year_levels,id',
            'semester_id' => 'required|exists:semesters,id',
            'course_code' => 'required|string|max:10|unique:curricula,course_code',
            'subject_name' => 'required|string|max:255',
            'description' => 'required|string',
            'lecture_units' => 'required|string',
            'lab_units' => 'required|string',
            'total_units' => 'required|integer|min:0',
        ], [
            'year_level_id.required' => 'The year level field is required.',
            'year_level_id.exists' => 'The selected year level is invalid.',
            'semester_id.required' => 'The semester field is required.',
            'semester_id.exists' => 'The selected semester is invalid.',
            'course_code.required' => 'The course code field is required.',
            'course_code.unique' => 'This course code is already taken.',
            'subject_name.required' => 'The subject name field is required.',
            'description.required' => 'The description field is required.',
            'lecture_units.required' => 'The lecture units field is required.',
            'lab_units.required' => 'The laboratory units field is required.',
            'total_units.required' => 'The total units field is required.',
            'total_units.integer' => 'The total units must be a whole number.',
            'total_units.min' => 'The total units must be at least 0.',
        ]);

        try {
            $subject = Curriculum::create($request->all());
            $subject->load(['yearLevel', 'semester']);
            
            session()->flash('success', 'Subject added successfully');
            return redirect()->back();

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add subject');
            return redirect()->back();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'year_level_id' => 'required|exists:year_levels,id',
            'semester_id' => 'required|exists:semesters,id',
            'course_code' => 'required|string|max:10|unique:curricula,course_code,' . $id,
            'subject_name' => 'required|string|max:255',
            'description' => 'required|string',
            'lecture_units' => 'required|string',
            'lab_units' => 'required|string',
            'total_units' => 'required|integer|min:0',
        ], [
            'year_level_id.required' => 'The year level field is required.',
            'year_level_id.exists' => 'The selected year level is invalid.',
            'semester_id.required' => 'The semester field is required.',
            'semester_id.exists' => 'The selected semester is invalid.',
            'course_code.required' => 'The course code field is required.',
            'course_code.unique' => 'This course code is already taken.',
            'subject_name.required' => 'The subject name field is required.',
            'description.required' => 'The description field is required.',
            'lecture_units.required' => 'The lecture units field is required.',
            'lab_units.required' => 'The laboratory units field is required.',
            'total_units.required' => 'The total units field is required.',
            'total_units.integer' => 'The total units must be a whole number.',
            'total_units.min' => 'The total units must be at least 0.',
        ]);

        try {
            $subject = Curriculum::findOrFail($id);
            $subject->update($request->all());
            
            session()->flash('success', 'Subject updated successfully');
            return redirect()->back();

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update subject');
            return redirect()->back();
        }
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
