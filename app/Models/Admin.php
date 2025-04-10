<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends Model
{
    /** @use HasFactory<\Database\Factories\AdminFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'phone_number',
    ];

    // protected $with = ['facultyLoads'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // public function facultyLoads(): HasMany
    // {
    //     return $this->hasMany(FacultyLoad::class)->with([
    //         'curriculum',
    //         'section',
    //         'yearLevel',
    //         'schedule',
    //         'room',
    //         'semester'
    //     ]);
    // }
}
