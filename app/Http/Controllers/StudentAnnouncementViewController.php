<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\TeacherAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentAnnouncementViewController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        // Get announcements that are either:
        // 1. Specifically for the student's section
        // 2. For all sections in the student's year level
        // 3. For all year levels (school-wide announcements)
        $announcements = TeacherAnnouncement::with(['sections', 'yearLevels'])
            ->where(function ($query) use ($student) {
                // Announcements for specific sections
                $query->whereHas('sections', function ($q) use ($student) {
                    $q->where('section_id', $student->section_id);
                });
            })
            ->orWhere(function ($query) use ($student) {
                // Announcements for all sections in the student's year level
                $query->whereDoesntHave('sections') // No specific sections assigned
                    ->whereHas('yearLevels', function ($q) use ($student) {
                        $q->where('year_level_id', $student->year_level_id);
                    });
            })
            ->orWhere(function ($query) {
                // School-wide announcements (no specific year levels or sections)
                $query->whereDoesntHave('yearLevels')
                    ->whereDoesntHave('sections');
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($announcement) {
                return [
                    'id' => $announcement->id,
                    'title_announcement' => $announcement->title_announcement,
                    'description_announcement' => $announcement->description_announcement,
                    'deadline_announcement' => $announcement->deadline_announcement,
                    'published_at' => $announcement->published_at,
                    'is_school_wide' => $announcement->yearLevels->isEmpty() && $announcement->sections->isEmpty(),
                    'is_year_level_wide' => !$announcement->yearLevels->isEmpty() && $announcement->sections->isEmpty(),
                    'is_section_specific' => !$announcement->sections->isEmpty(),
                ];
            });

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
