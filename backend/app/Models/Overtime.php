<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    use HasFactory;

    protected $table = 'overtimes';

    protected $fillable = [
        'user_id',
        'overtime_policy_id',
        'date',
        'hours',
        'compensation_type',
        'status',
        'compensated_hours',
        'compensation_date',
        'approver_id',
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
        'compensated_hours' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
