<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftAssignment extends Model
{
    use HasFactory;

    protected $table = 'shift_assignments';

    protected $fillable = [
        'user_id',
        'shift_id',
        'start_date',
        'end_date',
        'origin',
        'status',
        'priority',
        'note',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
