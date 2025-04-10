<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Section extends Model
{
    /** @use HasFactory<\Database\Factories\SectionFactory> */
    use HasFactory;

    protected $fillable = [
        'section',
        'year_level_id',
        'school_year_id',
        'minimum_number_students',
        'maximum_number_students'
    ];

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }


    public function yearLevel(): BelongsTo
    {
        return $this->belongsTo(YearLevel::class);
    }

    public function announcements(): BelongsToMany
    {
        return $this->belongsToMany(AdminAnnouncement::class, 'announcement_section');
    }

    public function studentGrades(): HasMany
    {
        return $this->hasMany(StudentGrade::class);
    }

    public function facultyLoads(): HasMany
    {
        return $this->hasMany(FacultyLoad::class);
    }

}
