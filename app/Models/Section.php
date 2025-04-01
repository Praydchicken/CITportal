<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Section extends Model
{
    /** @use HasFactory<\Database\Factories\SectionFactory> */
    use HasFactory;

    protected $fillable = ['year_level_id', 'section', 'minimum_number_students', 'maximum_number_students'];

    public function yearLevel(): BelongsTo
    {
        return $this->belongsTo(YearLevel::class); // Each section belongs to a year level
    }
}
