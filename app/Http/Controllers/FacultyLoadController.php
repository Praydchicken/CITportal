<?php

namespace App\Http\Controllers;

use App\Models\FacultyLoad;
use App\Http\Requests\StoreFacultyLoadRequest;
use App\Http\Requests\UpdateFacultyLoadRequest;
use Inertia\Inertia;
use App\Models\Teacher;
use App\Models\Section;
use App\Models\YearLevel;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Curriculum;
use App\Models\Student;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;

class FacultyLoadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teachers = Teacher::with([
            'user',
            'facultyLoads',
            'facultyLoads.curriculum',
            'facultyLoads.section',
            'facultyLoads.yearLevel',
            'facultyLoads.schedule',
            'facultyLoads.semester'
        ])->get();

        $sections = Section::with(['yearLevel', 'semester']) // Add semester relationship
            ->select('sections.*')
            ->join('year_levels', 'sections.year_level_id', '=', 'year_levels.id')
            ->get()
            ->map(function ($section) {
                return [
                    'id' => $section->id,
                    'section' => $section->section,
                    'year_level' => $section->yearLevel->year_level,
                    'year_level_id' => $section->year_level_id,
                    'semester_id' => $section->semester_id, // Add this line
                    'school_year_id' => $section->school_year_id
                ];
            });
        $yearLevels = YearLevel::all();

        // Get school years and active school year
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();

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

        $semesters = Semester::all();

        return Inertia::render('AdminDashboard/FacultyLoad', [
            'title' => 'Faculty Load',
            'teachers' => $teachers,
            'sections' => $sections,
            'yearLevels' => $yearLevels,
            'curricula' => $curricula,
            'schoolYears' => $schoolYears,
            'activeSchoolYear' => $activeSchoolYear,
            'semesters' => $semesters
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
            'teacher_id' => 'required|exists:teachers,id',
            'curriculum_id' => 'required|exists:curricula,id',
            'section_id' => 'required|exists:sections,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'semester_id' => 'required|exists:semesters,id'
        ]);

        // Get active school year
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();
        if (!$activeSchoolYear) {
            return back()->withErrors(['message' => 'Cannot create faculty load: No active school year set.']);
        }

        // Get the requested section (might be from previous year)
        $requestedSection = Section::findOrFail($validated['section_id']);

        try {
            DB::beginTransaction();

            // Find or create matching section in current active school year
            $activeSection = Section::firstOrCreate([
                'section' => $requestedSection->section,
                'year_level_id' => $validated['year_level_id'],
                'semester_id' => $validated['semester_id'],
                'school_year_id' => $activeSchoolYear->id
            ], [
                'minimum_number_students' => $requestedSection->minimum_number_students,
                'maximum_number_students' => $requestedSection->maximum_number_students
            ]);

            // Check for duplicate subject assignment (same teacher, same section, same subject, same day)
            $duplicateSubject = FacultyLoad::where('teacher_id', $validated['teacher_id'])
                ->where('section_id', $activeSection->id)
                ->where('curriculum_id', $validated['curriculum_id'])
                ->where('school_year_id', $activeSchoolYear->id)
                ->whereHas('schedule', function ($query) use ($validated) {
                    $query->where('day', $validated['day']);
                })
                ->first();

            if ($duplicateSubject) {
                DB::rollBack();
                return back()->withErrors(['message' => 'Duplicate subject: This teacher is already teaching this subject to this section on the same day.']);
            }

            // Create the class schedule
            $classSchedule = ClassSchedule::create([
                'day' => $validated['day'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time']
            ]);

            // Check for teacher schedule conflicts (same teacher, same time)
            $teacherConflict = FacultyLoad::join('class_schedules', 'faculty_loads.class_schedule_id', '=', 'class_schedules.id')
                ->where('faculty_loads.teacher_id', $validated['teacher_id'])
                ->where('class_schedules.day', $validated['day'])
                ->where('faculty_loads.school_year_id', $activeSchoolYear->id)
                ->where(function ($query) use ($validated) {
                    $query->where(function ($q) use ($validated) {
                        $q->where('class_schedules.start_time', '<=', $validated['start_time'])
                            ->where('class_schedules.end_time', '>', $validated['start_time']);
                    })->orWhere(function ($q) use ($validated) {
                        $q->where('class_schedules.start_time', '<', $validated['end_time'])
                            ->where('class_schedules.end_time', '>=', $validated['end_time']);
                    })->orWhere(function ($q) use ($validated) {
                        $q->where('class_schedules.start_time', '>=', $validated['start_time'])
                            ->where('class_schedules.end_time', '<=', $validated['end_time']);
                    });
                })
                ->first();

            if ($teacherConflict) {
                DB::rollBack();
                return back()->withErrors(['message' => 'Schedule conflict: This teacher already has a class during this time slot.']);
            }

            // Check for section schedule conflicts (same section, same time but different teacher)
            $sectionConflict = FacultyLoad::join('class_schedules', 'faculty_loads.class_schedule_id', '=', 'class_schedules.id')
                ->where('faculty_loads.section_id', $activeSection->id)
                ->where('class_schedules.day', $validated['day'])
                ->where('faculty_loads.school_year_id', $activeSchoolYear->id)
                ->where(function ($query) use ($validated) {
                    $query->where(function ($q) use ($validated) {
                        $q->where('class_schedules.start_time', '<=', $validated['start_time'])
                            ->where('class_schedules.end_time', '>', $validated['start_time']);
                    })->orWhere(function ($q) use ($validated) {
                        $q->where('class_schedules.start_time', '<', $validated['end_time'])
                            ->where('class_schedules.end_time', '>=', $validated['end_time']);
                    })->orWhere(function ($q) use ($validated) {
                        $q->where('class_schedules.start_time', '>=', $validated['start_time'])
                            ->where('class_schedules.end_time', '<=', $validated['end_time']);
                    });
                })
                ->first();

            if ($sectionConflict) {
                DB::rollBack();
                return back()->withErrors(['message' => 'Schedule conflict: This section already has a class scheduled during this time slot.']);
            }

            // Conflict for same section and curriculum with different teachers (for update)
            $sameSubjectConflict = FacultyLoad::where('school_year_id', $activeSchoolYear->id)
                ->where('section_id', $validated['section_id'])
                ->where('curriculum_id', $validated['curriculum_id'])
                ->where('teacher_id', '!=', $validated['teacher_id'])
                ->exists();

            if ($sameSubjectConflict) {
                DB::rollBack();
                return back()->withErrors(['message' => 'Conflict: Another teacher is already assigned to this section with the same subject.']);
            }

            // Create the faculty load with school year
            $facultyLoad = FacultyLoad::create([
                'teacher_id' => $validated['teacher_id'],
                'curriculum_id' => $validated['curriculum_id'],
                'section_id' => $activeSection->id,
                'year_level_id' => $validated['year_level_id'],
                'class_schedule_id' => $classSchedule->id,
                'semester_id' => $validated['semester_id'],
                'school_year_id' => $activeSchoolYear->id
            ]);

            // Automatically Assign Students to this New Load
            $studentsToAssign = Student::where('year_level_id', $validated['year_level_id'])
                ->where('semester_id', $validated['semester_id'])
                ->where('section_id', $activeSection->id)
                ->where('school_year_id', $activeSchoolYear->id)
                ->whereHas('status', function ($query) {
                    $query->where('status_name', 'Enrolled');
                })
                ->pluck('id');

            $studentLoadData = [];
            foreach ($studentsToAssign as $studentId) {
                $studentLoadData[] = [
                    'student_id' => $studentId,
                    'faculty_load_id' => $facultyLoad->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Bulk insert for efficiency
            if (!empty($studentLoadData)) {
                DB::table('student_loads')->insert($studentLoadData);
            }

            DB::commit();

            return redirect()->back()->with([
                'success' => 'Faculty load created successfully and assigned to relevant students.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['message' => 'An error occurred while creating the faculty load: ' . $e->getMessage()]);
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
            'teacher_id' => 'required|exists:teachers,id',
            'curriculum_id' => 'required|exists:curricula,id',
            'section_id' => 'required|exists:sections,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'day' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'semester_id' => 'required|exists:semesters,id'
        ]);

        // Get active school year
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();
        if (!$activeSchoolYear) {
            return back()->withErrors(['message' => 'Cannot update faculty load: No active school year set.']);
        }

        try {
            DB::beginTransaction();

            // Check for duplicate subject assignment (same teacher, same section, same subject, same day)
            $duplicateSubject = FacultyLoad::where('teacher_id', $validated['teacher_id'])
                ->where('section_id', $validated['section_id'])
                ->where('curriculum_id', $validated['curriculum_id'])
                ->where('school_year_id', $activeSchoolYear->id)
                ->where('id', '!=', $facultyLoad->id) // Exclude current load being updated
                ->whereHas('schedule', function ($query) use ($validated) {
                    $query->where('day', $validated['day']);
                })
                ->first();

            if ($duplicateSubject) {
                DB::rollBack();
                return back()->withErrors(['message' => 'Duplicate subject: This teacher is already teaching this subject to this section on the same day.']);
            }

            // Update or create class schedule
            $classSchedule = ClassSchedule::updateOrCreate(
                ['id' => $facultyLoad->class_schedule_id],
                [
                    'day' => $validated['day'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time']
                ]
            );

            // Check for teacher schedule conflicts (same teacher, same time, different load)
            $teacherConflict = FacultyLoad::join('class_schedules', 'faculty_loads.class_schedule_id', '=', 'class_schedules.id')
                ->where('faculty_loads.teacher_id', $validated['teacher_id'])
                ->where('faculty_loads.id', '!=', $facultyLoad->id)
                ->where('class_schedules.day', $validated['day'])
                ->where('faculty_loads.school_year_id', $activeSchoolYear->id)
                ->where(function ($query) use ($validated) {
                    $query->where(function ($q) use ($validated) {
                        $q->where('class_schedules.start_time', '<=', $validated['start_time'])
                            ->where('class_schedules.end_time', '>', $validated['start_time']);
                    })->orWhere(function ($q) use ($validated) {
                        $q->where('class_schedules.start_time', '<', $validated['end_time'])
                            ->where('class_schedules.end_time', '>=', $validated['end_time']);
                    })->orWhere(function ($q) use ($validated) {
                        $q->where('class_schedules.start_time', '>=', $validated['start_time'])
                            ->where('class_schedules.end_time', '<=', $validated['end_time']);
                    });
                })
                ->first();

            if ($teacherConflict) {
                DB::rollBack();
                return back()->withErrors(['message' => 'Schedule conflict: This teacher already has another class during this time slot.']);
            }

            // Check for section schedule conflicts (same section, same time, different load)
            $sectionConflict = FacultyLoad::join('class_schedules', 'faculty_loads.class_schedule_id', '=', 'class_schedules.id')
                ->where('faculty_loads.section_id', $validated['section_id'])
                ->where('faculty_loads.id', '!=', $facultyLoad->id)
                ->where('class_schedules.day', $validated['day'])
                ->where('faculty_loads.school_year_id', $activeSchoolYear->id)
                ->where(function ($query) use ($validated) {
                    $query->where(function ($q) use ($validated) {
                        $q->where('class_schedules.start_time', '<=', $validated['start_time'])
                            ->where('class_schedules.end_time', '>', $validated['start_time']);
                    })->orWhere(function ($q) use ($validated) {
                        $q->where('class_schedules.start_time', '<', $validated['end_time'])
                            ->where('class_schedules.end_time', '>=', $validated['end_time']);
                    })->orWhere(function ($q) use ($validated) {
                        $q->where('class_schedules.start_time', '>=', $validated['start_time'])
                            ->where('class_schedules.end_time', '<=', $validated['end_time']);
                    });
                })
                ->first();

            if ($sectionConflict) {
                DB::rollBack();
                return back()->withErrors(['message' => 'Schedule conflict: This section already has another class scheduled during this time slot.']);
            }

            // Conflict for same section and curriculum with different teachers (for update)
            $sameSubjectConflict = FacultyLoad::where('school_year_id', $activeSchoolYear->id)
                ->where('section_id', $validated['section_id'])
                ->where('curriculum_id', $validated['curriculum_id'])
                ->where('teacher_id', '!=', $validated['teacher_id'])
                ->exists();

            if ($sameSubjectConflict) {
                DB::rollBack();
                return back()->withErrors(['message' => 'Conflict: Another teacher is already assigned to this section with the same subject.']);
            }


            // Update faculty load with the correct IDs and school year
            $facultyLoad->update([
                'teacher_id' => $validated['teacher_id'],
                'curriculum_id' => $validated['curriculum_id'],
                'section_id' => $validated['section_id'],
                'year_level_id' => $validated['year_level_id'],
                'class_schedule_id' => $classSchedule->id,
                'semester_id' => $validated['semester_id'],
                'school_year_id' => $activeSchoolYear->id
            ]);

            DB::commit();

            return back()->with([
                'success' => 'Faculty load updated successfully.'
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

            // Get the schedule ID before deleting the faculty load
            $scheduleId = $facultyLoad->class_schedule_id;

            // Delete the faculty load
            $facultyLoad->delete();

            // Delete the associated schedule if it exists
            if ($scheduleId) {
                ClassSchedule::where('id', $scheduleId)->delete();
            }

            DB::commit();

            return back()->with([
                'success' => 'Schedule deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Faculty Load Deletion Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return back()->withErrors(['message' => 'An error occurred while deleting the schedule.']);
        }
    }
}
