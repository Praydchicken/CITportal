<?php

namespace App\Http\Controllers;

use App\Models\FacultyLoad;
use App\Http\Requests\StoreFacultyLoadRequest;
use App\Http\Requests\UpdateFacultyLoadRequest;
use Inertia\Inertia;
use App\Models\Admin;
use App\Models\Section;
use App\Models\YearLevel;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Curriculum;
use Illuminate\Support\Facades\DB;

class FacultyLoadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = Admin::with([
            'user',
            'facultyLoads',
            'facultyLoads.curriculum',
            'facultyLoads.section',
            'facultyLoads.yearLevel',
            'facultyLoads.schedule',
            'facultyLoads.room'
        ])->get();

        $sections = Section::with('yearLevel')
            ->select('sections.*')
            ->join('year_levels', 'sections.year_level_id', '=', 'year_levels.id')
            ->get()
            ->map(function ($section) {
                return [
                    'id' => $section->id,
                    'section' => $section->section,
                    'year_level' => $section->yearLevel->year_level,
                    'year_level_id' => $section->year_level_id
                ];
            });
        $yearLevels = YearLevel::all();
        
        // Get curricula with their relationships
        $curricula = Curriculum::with(['year_level', 'semester'])
            ->orderBy('year_level_id')
            ->orderBy('semester_id')
            ->get()
            ->map(function ($curriculum) {
                return [
                    'id' => $curriculum->id,
                    'course_code' => $curriculum->course_code,
                    'subject_name' => $curriculum->subject_name,
                    'year_level_id' => $curriculum->year_level_id,
                    'semester_id' => $curriculum->semester_id,
                    'year_level' => $curriculum->year_level->year_level,
                    'semester' => $curriculum->semester->semester_name
                ];
            });

        return Inertia::render('AdminDashboard/FacultyLoad', [
            'title' => 'Faculty Load',
            'admins' => $admins,
            'sections' => $sections,
            'yearLevels' => $yearLevels,
            'curricula' => $curricula
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
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'faculty_id' => 'required|exists:admins,id',
            'curriculum_id' => 'required|exists:curricula,id',
            'section_id' => 'required|exists:sections,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'class_room_id' => 'required|string',
            'semester_id' => 'required|exists:semesters,id'
        ]);

        try {
            DB::beginTransaction();

            // Create the class schedule
            $classSchedule = ClassSchedule::create([
                'day' => $validated['day'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time']
            ]);

            // Find or create the classroom
            $classRoom = ClassRoom::firstOrCreate(
                ['room_name' => $validated['class_room_id']],
                ['status' => 'Active']
            );

            // Check for schedule conflicts
            $existingLoad = FacultyLoad::where(function($query) use ($validated, $classSchedule) {
                $query->where('admin_id', $validated['faculty_id'])
                      ->where('class_schedule_id', $classSchedule->id);
            })->orWhere(function($query) use ($validated, $classSchedule, $classRoom) {
                $query->where('class_schedule_id', $classSchedule->id)
                      ->where('class_room_id', $classRoom->id);
            })->first();

            if ($existingLoad) {
                DB::rollBack();
                return back()->withErrors(['message' => 'Schedule conflict detected. Please choose a different time or room.']);
            }

            // Create the faculty load
            $facultyLoad = FacultyLoad::create([
                'admin_id' => $validated['faculty_id'],
                'curriculum_id' => $validated['curriculum_id'],
                'section_id' => $validated['section_id'],
                'year_level_id' => $validated['year_level_id'],
                'class_schedule_id' => $classSchedule->id,
                'class_room_id' => $classRoom->id,
                'semester_id' => $validated['semester_id']
            ]);

            // Get the updated admin data with all necessary relationships
            $admins = Admin::with([
                'user',
                'facultyLoads',
                'facultyLoads.curriculum',
                'facultyLoads.section',
                'facultyLoads.yearLevel',
                'facultyLoads.schedule',
                'facultyLoads.room'
            ])->get();

            DB::commit();

            return redirect()->back()->with([
                'success' => 'Faculty load created successfully.',
                'admins' => $admins
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Faculty Load Creation Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            if (config('app.debug')) {
                return back()->withErrors(['message' => 'Error: ' . $e->getMessage()]);
            }
            
            return back()->withErrors(['message' => 'An error occurred while creating the faculty load.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FacultyLoad $facultyLoad)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FacultyLoad $facultyLoad)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FacultyLoad $facultyLoad)
    {
        $validated = $request->validate([
            'faculty_id' => 'required|exists:admins,id',
            'curriculum_id' => 'required|exists:curricula,id',
            'section_id' => 'required|exists:sections,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'day' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'class_room_id' => 'required|string',
            'semester_id' => 'required|exists:semesters,id'
        ]);

        try {
            DB::beginTransaction();

            // Update or create class schedule
            $classSchedule = ClassSchedule::updateOrCreate(
                ['id' => $facultyLoad->class_schedule_id],
                [
                    'day' => $validated['day'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time']
                ]
            );

            // Update or create classroom
            $classRoom = ClassRoom::firstOrCreate(
                ['room_name' => $validated['class_room_id']],
                [
                    'room_name' => $validated['class_room_id'],
                    'status' => 'Available' // Set default status for new rooms
                ]
            );

            // Update faculty load with the correct IDs
            $facultyLoad->update([
                'admin_id' => $validated['faculty_id'],
                'curriculum_id' => $validated['curriculum_id'],
                'section_id' => $validated['section_id'],
                'year_level_id' => $validated['year_level_id'],
                'class_schedule_id' => $classSchedule->id,
                'class_room_id' => $classRoom->id,
                'semester_id' => $validated['semester_id']
            ]);

            // Get the updated admin data with all necessary relationships
            $admins = Admin::with([
                'user',
                'facultyLoads',
                'facultyLoads.curriculum',
                'facultyLoads.section',
                'facultyLoads.yearLevel',
                'facultyLoads.schedule',
                'facultyLoads.room',
                'facultyLoads.semester'
            ])->get();

            DB::commit();

            return back()->with([
                'success' => 'Faculty load updated successfully.',
                'admins' => $admins
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Faculty Load Update Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return back()->withErrors(['message' => 'An error occurred while updating the faculty load: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FacultyLoad $facultyLoad)
    {
        try {
            DB::beginTransaction();

            // Get the schedule and room IDs before deleting the faculty load
            $scheduleId = $facultyLoad->class_schedule_id;
            $roomId = $facultyLoad->class_room_id;

            // Delete the faculty load
            $facultyLoad->delete();

            // Delete the associated schedule if it exists
            if ($scheduleId) {
                ClassSchedule::where('id', $scheduleId)->delete();
            }

            // Get the updated admin data with all necessary relationships
            $admins = Admin::with([
                'user',
                'facultyLoads',
                'facultyLoads.curriculum',
                'facultyLoads.section',
                'facultyLoads.yearLevel',
                'facultyLoads.schedule',
                'facultyLoads.room',
                'facultyLoads.semester'
            ])->get();

            DB::commit();

            return back()->with([
                'success' => 'Schedule deleted successfully.',
                'admins' => $admins
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Faculty Load Deletion Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return back()->withErrors(['message' => 'An error occurred while deleting the schedule.']);
        }
    }
}
