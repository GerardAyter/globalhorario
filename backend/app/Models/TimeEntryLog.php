<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCompanySharding;

class TimeEntryLog extends Model
{
    use HasCompanySharding;

    protected static string $shardBaseTable = 'time_entry_logs';

    public $timestamps = false;

    protected $fillable = [
        'time_entry_id',
        'action',
        'happened_at',
        'user_id',
        'employee_id',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'happened_at' => 'datetime',
        'created_at'  => 'datetime',
        'metadata'    => 'array',
    ];

    public function timeEntry()
    {
        return $this->belongsTo(TimeEntry::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
