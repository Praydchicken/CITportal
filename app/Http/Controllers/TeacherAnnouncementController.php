<?php

namespace App\Http\Controllers;

use App\Models\TeacherAnnouncement;
use App\Http\Requests\StoreTeacherAnnouncementRequest;
use App\Http\Requests\UpdateTeacherAnnouncementRequest;
use Inertia\Inertia;
use App\Models\YearLevel;
use App\Models\Section;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TeacherAnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $yearLevels = YearLevel::all(['id', 'year_level']);
        
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

        $announcements = TeacherAnnouncement::with(['yearLevels', 'sections'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title_announcement,
                    'description' => $announcement->description_announcement,
                    'deadline' => $announcement->deadline_announcement,
                    'published_at' => $announcement->published_at,
                    'year_levels' => $announcement->yearLevels->pluck('year_level'),
                    'sections' => $announcement->sections->pluck('section')
                ];
            });

        return Inertia::render('TeacherDashboard/TeacherAnnouncement', [
            'title' => 'Teacher Announcement',
            'sections' => $sections,
            'yearLevels' => $yearLevels,
            'announcements' => $announcements,
            'flash' => [
                'success' => session('success'),
                'error' => session('error')
            ],
             'auth' => [
                    'user' => [
                        'name' => "{$teacher->first_name} {$teacher->last_name} | Teacher",
                        'teacher' => $teacher
                    ]
                ],
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
        $request->validate([
            'title_announcement' => 'required|string|max:255',
            'description_announcement' => 'required|string',
            'deadline_announcement' => 'required|date',
            'year_level_id' => 'required',
            'section_id' => 'required'
        ]);

        try {
            DB::beginTransaction();

            // Create the announcement
            $announcement = TeacherAnnouncement::create([
                'title_announcement' => $request->title_announcement,
                'description_announcement' => $request->description_announcement,
                'deadline_announcement' => $request->deadline_announcement,
                'published_at' => now()
            ]);

            if ($request->year_level_id === 'all') {
                // Get all year levels
                $yearLevels = YearLevel::all();
                
                // Insert all year levels
                foreach ($yearLevels as $yearLevel) {
                    DB::table('announcement_year_levels')->insert([
                        'teacher_announcements_id' => $announcement->id,
                        'year_level_id' => $yearLevel->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    if ($request->section_id === 'all') {
                        // Get all sections for this year level
                        $sections = Section::where('year_level_id', $yearLevel->id)->get();
                        foreach ($sections as $section) {
                            DB::table('announcement_sections')->insert([
                                'teacher_announcements_id' => $announcement->id,
                                'section_id' => $section->id,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            } else {
                // Get the specific year level
                $yearLevel = YearLevel::findOrFail($request->year_level_id);

                // Insert into announcement_year_levels table
                DB::table('announcement_year_levels')->insert([
                    'teacher_announcements_id' => $announcement->id,
                    'year_level_id' => $yearLevel->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                if ($request->section_id === 'all') {
                    // Get all sections for this year level
                    $sections = Section::where('year_level_id', $yearLevel->id)->get();
                } else {
                    // Get specific section(s) for the selected year level
                    $sections = Section::where('year_level_id', $yearLevel->id)
                                     ->where('section', $request->section_id)
                                     ->get();
                }

                if ($sections->isEmpty()) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Selected section not found for this year level');
                }

                // Insert into announcement_sections table
                foreach ($sections as $section) {
                    DB::table('announcement_sections')->insert([
                        'teacher_announcements_id' => $announcement->id,
                        'section_id' => $section->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Announcement created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Announcement creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create announcement: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TeacherAnnouncement $TeacherAnnouncement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TeacherAnnouncement $TeacherAnnouncement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeacherAnnouncement $teacherAnnouncement)
    {
        $request->validate([
            'title_announcement' => 'required|string|max:255',
            'description_announcement' => 'required|string',
            'deadline_announcement' => 'required|date',
            'year_level_id' => 'required',
            'section_id' => 'required'
        ]);

        try {
            DB::beginTransaction();

            // Update the announcement
            $teacherAnnouncement->update([
                'title_announcement' => $request->title_announcement,
                'description_announcement' => $request->description_announcement,
                'deadline_announcement' => $request->deadline_announcement
            ]);

            // Delete existing relationships
            DB::table('announcement_year_levels')->where('teacher_announcements_id', $teacherAnnouncement->id)->delete();
            DB::table('announcement_sections')->where('teacher_announcements_id', $teacherAnnouncement->id)->delete();

            if ($request->year_level_id === 'all') {
                // Get all year levels
                $yearLevels = YearLevel::all();
                
                // Insert all year levels
                foreach ($yearLevels as $yearLevel) {
                    DB::table('announcement_year_levels')->insert([
                        'teacher_announcements_id' => $teacherAnnouncement->id,
                        'year_level_id' => $yearLevel->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    if ($request->section_id === 'all') {
                        // Get all sections for this year level
                        $sections = Section::where('year_level_id', $yearLevel->id)->get();
                        foreach ($sections as $section) {
                            DB::table('announcement_sections')->insert([
                                'teacher_announcements_id' => $teacherAnnouncement->id,
                                'section_id' => $section->id,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            } else {
                // Get the specific year level
                $yearLevel = YearLevel::findOrFail($request->year_level_id);

                // Insert into announcement_year_levels table
                DB::table('announcement_year_levels')->insert([
                    'teacher_announcements_id' => $teacherAnnouncement->id,
                    'year_level_id' => $yearLevel->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                if ($request->section_id === 'all') {
                    // Get all sections for this year level
                    $sections = Section::where('year_level_id', $yearLevel->id)->get();
                } else {
                    // Get specific section(s) for the selected year level
                    $sections = Section::where('year_level_id', $yearLevel->id)
                                     ->where('section', $request->section_id)
                                     ->get();
                }

                if ($sections->isEmpty()) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Selected section not found for this year level');
                }

                // Insert into announcement_sections table
                foreach ($sections as $section) {
                    DB::table('announcement_sections')->insert([
                        'teacher_announcements_id' => $teacherAnnouncement->id,
                        'section_id' => $section->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Announcement updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Announcement update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update announcement: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeacherAnnouncement $teacherAnnouncement)
    {
        try {
            DB::beginTransaction();

            // Delete related records from pivot tables first
            DB::table('announcement_year_levels')->where('teacher_announcements_id', $teacherAnnouncement->id)->delete();
            DB::table('announcement_sections')->where('teacher_announcements_id', $teacherAnnouncement->id)->delete();

            // Delete the announcement
            $teacherAnnouncement->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Announcement deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Announcement deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete announcement');
        }
    }
}
