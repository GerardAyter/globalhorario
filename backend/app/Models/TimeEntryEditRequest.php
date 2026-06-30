<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeEntryEditRequest extends Model
{
    protected $table = 'time_entry_edit_requests';

    protected $fillable = [
        'type',
        'initiated_by',
        'time_entry_id',
        'break_id',
        'employee_id',
        'requested_by_user_id',
        'reason',
        'original_data',
        'requested_data',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'original_data'  => 'array',
        'requested_data' => 'array',
        'reviewed_at'    => 'datetime',
    ];

    public function timeEntry()
    {
        return $this->belongsTo(TimeEntry::class);
    }

    public function timeEntryBreak()
    {
        return $this->belongsTo(TimeEntryBreak::class, 'break_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
