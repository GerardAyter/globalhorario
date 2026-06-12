<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftConflict extends Model
{
    use HasFactory;

    protected $table = 'shift_conflicts';

    protected $fillable = [
        'user_id',
        'assignment_a_id',
        'assignment_b_id',
        'type',
        'detected_at',
        'resolution',
        'resolution_note',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];
}
