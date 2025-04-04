<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];


    protected $fillable = [
        'user_id',
        'section_id',
        'year_level_id',
        'student_status_id',
        'student_number',
        'first_name',
        'middle_name',
        'last_name',
        'phone_number',
        'gender',
        'address',
        'enrollment_date',
    ];

     public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function section(): BelongsTo{
        return $this->belongsTo(Section::class);
    }

    public function yearLevel(): BelongsTo{
        return $this->belongsTo(YearLevel::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StudentStatus::class, 'student_status_id');
    }

    protected static function booted()
    {
        static::deleting(function ($student) {
            if ($student->user) {
                $student->user->delete(); // Delete the user account only
            }
        });
    }
}
