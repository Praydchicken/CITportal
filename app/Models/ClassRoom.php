<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassRoom extends Model
{
    /** @use HasFactory<\Database\Factories\ClassRoomFactory> */
    use HasFactory;

    protected $fillable = [
        'room_name',
        'status',
    ];

    // public function facultyLoads(): HasMany
    // {
    //     return $this->hasMany(FacultyLoad::class);
    // }
}
