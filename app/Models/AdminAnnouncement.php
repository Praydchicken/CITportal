<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdminAnnouncement extends Model
{
    /** @use HasFactory<\Database\Factories\AdminAnnouncementFactory> */
    use HasFactory;

    protected $fillable = [
        'title_announcement', 
        'description_announcement', 
        'deadline_announcement',  
        'published_at'
    ];

    public function yearLevels(): BelongsToMany
    {
        return $this->belongsToMany(YearLevel::class, 'announcement_year_levels', 'admin_announcements_id', 'year_level_id');
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'announcement_sections', 'admin_announcements_id', 'section_id');
    }
}
