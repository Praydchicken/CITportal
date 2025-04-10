<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentStatus extends Model
{
    /** @use HasFactory<\Database\Factories\StudentStatusFactory> */
    use HasFactory;

    protected $fillable = [
        'status_name'
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

}
