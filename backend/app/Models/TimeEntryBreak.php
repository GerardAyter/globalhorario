<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCompanySharding;

class TimeEntryBreak extends Model
{
    use HasCompanySharding;

    protected static string $shardBaseTable = 'time_entry_breaks';

    protected $fillable = [
        'time_entry_id',
        'break_start_at',
        'break_end_at',
        'duration_minutes',
    ];

    protected $casts = [
        'break_start_at' => 'datetime',
        'break_end_at'   => 'datetime',
    ];

    public function timeEntry()
    {
        return $this->belongsTo(TimeEntry::class);
    }
}
