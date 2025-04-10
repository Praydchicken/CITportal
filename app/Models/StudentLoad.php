<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLoad extends Model
{
    protected $fillable = [
        'student_id',
        'faculty_load_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function facultyLoad(): BelongsTo
    {
        return $this->belongsTo(FacultyLoad::class);
    }

}
