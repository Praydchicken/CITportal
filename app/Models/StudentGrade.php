<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGrade extends Model
{
    /** @use HasFactory<\Database\Factories\StudentGradeFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'curriculum_id',
        'school_year_id',
        'semester_id',
        'section_id',
        'year_level_id',
        'prelim_grade',
        'midterm_grade',
        'final_grade',
        'raw_grade',
        'gwa_equivalent',
        'total_gwa',
        'grade_remarks',
        'grade_status',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }


    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class, 'curriculum_id');
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function yearLevel(): BelongsTo
    {
        return $this->belongsTo(YearLevel::class);
    }

}
