<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacultyLoad extends Model
{
    /** @use HasFactory<\Database\Factories\FacultyLoadFactory> */
    use HasFactory;

    protected $fillable = [
        // 'admin_id',
        'teacher_id',
        'curriculum_id',
        'section_id',
        'year_level_id',
        'class_schedule_id',
        'semester_id',
        'school_year_id',
    ];

    // public function admin(): BelongsTo
    // {
    //     return $this->belongsTo(Admin::class); // assuming User model for admin
    // }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function yearLevel(): BelongsTo
    {
        return $this->belongsTo(YearLevel::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'class_schedule_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function studentLoads(): HasMany
    {
        return $this->hasMany(StudentLoad::class);
    }

    public function classSchedule()
    {
        return $this->belongsTo(ClassSchedule::class, 'class_schedule_id');
    }

}
