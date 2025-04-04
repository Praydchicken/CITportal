<?php

namespace App\Http\Controllers;

use App\Models\AdminAnnouncement;
use App\Http\Requests\StoreAdminAnnouncementRequest;
use App\Http\Requests\UpdateAdminAnnouncementRequest;
use Inertia\Inertia;
use App\Models\YearLevel;
use App\Models\Section;

class AdminAnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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

        return Inertia::render('AdminDashboard/AdminAnnouncement', [
            'title' => 'Admin Announcement',
            'sections' => $sections
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
    public function store(StoreAdminAnnouncementRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AdminAnnouncement $adminAnnouncement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdminAnnouncement $adminAnnouncement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminAnnouncementRequest $request, AdminAnnouncement $adminAnnouncement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdminAnnouncement $adminAnnouncement)
    {
        //
    }
}
