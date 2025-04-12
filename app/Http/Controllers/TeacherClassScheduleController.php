<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminAnnouncement;

class TeacherClassScheduleController extends Controller
{
    /**
     * Display the teacher's class schedule.
     */
    public function index()
    {
        // Get authenticated user
        $user = Auth::user();

        // Get teacher record with relationships
        $teacher = Teacher::where('user_id', $user->id)
            ->with([
                'facultyLoads.curriculum',
                'facultyLoads.section',
                'facultyLoads.yearLevel',
                'facultyLoads.schedule',
                'facultyLoads.semester'
            ])
            ->first();

        // Check if teacher exists
        if (!$teacher) {
            return redirect()->route('login');
        }

        // Get class schedule with all necessary details
        $classSchedule = $teacher->facultyLoads->map(function($load) {
            return [
                'subject' => $load->curriculum->subject_name,
                'course_code' => $load->curriculum->course_code,
                'section' => $load->section->section,
                'year_level' => $load->yearLevel->year_level,
                'day' => $load->schedule->day,
                'start_time' => $load->schedule->start_time,
                'end_time' => $load->schedule->end_time,
                'semester' => $load->semester->semester_name,
            ];
        });

        return Inertia::render('TeacherDashboard/TeacherClassSchedule', [
            'title' => 'Class Schedule',
            'classSchedule' => $classSchedule,
            'auth' => [
                'user' => [
                    'name' => "{$teacher->first_name} {$teacher->last_name} | Teacher",
                    'teacher' => $teacher
                ]
            ]
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
       
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
       
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
    }

}
