<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Curriculum extends Model
{
    /** @use HasFactory<\Database\Factories\CurriculumFactory> */
    use HasFactory;

    protected $fillable = [
        'year_level_id',
        'semester_id',
        'course_code',
        'subject_name',
        'description',
        'lecture_units',
        'lab_units',
        'total_units'
    ];

    public function yearLevel(): BelongsTo {
        return $this->belongsTo(YearLevel::class);
    }

    public function semester(): BelongsTo {
        return $this->belongsTo(Semester::class);
    }
}
