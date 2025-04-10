<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\ClassScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'day',
        'start_time',
        'end_time',
    ];

    public function facultyLoads(): HasMany
    {
        return $this->hasMany(FacultyLoad::class);
    }
}
