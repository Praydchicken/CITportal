<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialRecord extends Model
{
    protected $table = 'student_financials';
    protected $fillable = [
        'student_id',
        'school_year',
        'semester',
        'tuition_fee',
        'discount',
        'adjustment',
        'amount_paid',
        'balance',
    ];
}
