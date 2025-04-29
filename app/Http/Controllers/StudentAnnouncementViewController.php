<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\TeacherAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentAnnouncementViewController extends Controller
{
    public function index() {
        $user = Auth::user();

        $student = Student::where('user_id', $user->id)->first();

        $sectionAnnouncements = TeacherAnnouncement::whereHas('sections', function ($query) use ($student) {
                                        $query->where('section_id', $student->section_id);
                                    })->get();

        $yearLevelAnnouncements = TeacherAnnouncement::whereHas('yearLevels', function ($query) use ($student) {
                                            $query->where('year_level_id', $student->year_level_id);
                                        })->get();


        $announcements = TeacherAnnouncement::where(function ($query) use ($student) {
                                    $query->whereHas('sections', function ($q) use ($student) {
                                        $q->where('section_id', $student->section_id);
                                    })->orWhereHas('yearLevels', function ($q) use ($student) {
                                        $q->where('year_level_id', $student->year_level_id);
                                    });
                                })->get();


        // dd($announcements);
        return Inertia::render('StudentDashboard/StudentAnnouncementView', [
            'announcements' => $announcements,
             'auth' => [
                'user' => [
                    'name' => "{$student->first_name} {$student->last_name} | Student",
                    'student' => $student
                ]
            ]
        ]);
    }
}
