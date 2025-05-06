<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherFactory> */
    use HasFactory;

     protected $fillable = [
        'user_id',
        'school_year_id',
        'first_name',
        'middle_name',
        'last_name',
        'phone_number',
        'address',
    ];

     public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the school year where the teacher is active.
     */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    /**
     * Get the faculty loads (teaching assignments) of the teacher.
     */
    public function facultyLoads(): HasMany
    {
        return $this->hasMany(FacultyLoad::class);
    }
    /**
     * Get the semesters associated with the teacher.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(TeacherAnnouncement::class);
    }
    /**
     * Get the semesters associated with the teacher.
     */
    public function teachers(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
    

    public function studentGrades(): HasMany
    {
        return $this->hasMany(StudentGrade::class);
    }

    protected static function booted()
    {
        static::deleting(function ($teacher) {
            // Delete faculty loads first to avoid foreign key constraint issues
            $teacher->facultyLoads()->delete();
            
            // Delete the associated user account
            if ($teacher->user) {
                $teacher->user->delete();
            }
        });
    }
}
