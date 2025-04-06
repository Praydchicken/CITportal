<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YearLevel extends Model
{
    /** @use HasFactory<\Database\Factories\YearLevelFactory> */
    use HasFactory;

    protected $fillable = [
        'year_level',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'year_level_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class); // One year level has many sections
    }

    public function curricula(): HasMany 
    {
        return $this->hasMany(Curriculum::class);
    }

    public function studentGrades(): HasMany
    {
        return $this->hasMany(StudentGrade::class);
    }

}
