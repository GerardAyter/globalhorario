<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsenceRequest extends Model
{
    use HasFactory;

    protected $table = 'absence_requests';

    protected $fillable = [
        'user_id',
        'absence_type_id',
        'start_date',
        'end_date',
        'working_days',
        'half_day_start',
        'half_day_end',
        'status',
        'employee_comment',
        'manager_comment',
        'attachment_url',
        'approvers',
        'current_approval_index',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'working_days' => 'decimal:2',
        'half_day_start' => 'boolean',
        'half_day_end' => 'boolean',
        'approvers' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(AbsenceType::class, 'absence_type_id');
    }
}
