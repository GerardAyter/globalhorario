<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class TimeEntry extends Model
{
    use HasFactory;
    use HasTenant;

    const ORIGIN_MANUAL    = 'manual';
    const ORIGIN_MOBILE    = 'mobile';
    const ORIGIN_WEB       = 'web';
    const ORIGIN_WHATSAPP  = 'whatsapp';

    protected $table = 'time_entries';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_id',
        'company_id',
        'shift_id',
        'date',
        'work_status',
        'origin',
        'clock_in_at',
        'clock_out_at',
        'source',
        'clock_in_geo',
        'clock_out_geo',
        'distance_meters',
        'within_radius',
        'status',
        'validated_by',
        'validator_notes',
        'integrity_hash',
        'wa_event_id',
    ];

    protected $casts = [
        'date'         => 'date',
        'clock_in_at'  => 'datetime',
        'clock_out_at' => 'datetime',
        'clock_in_geo' => 'array',
        'clock_out_geo' => 'array',
        'within_radius' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function breaks()
    {
        return $this->hasMany(TimeEntryBreak::class)->orderBy('break_start_at');
    }

    public function logs()
    {
        return $this->hasMany(TimeEntryLog::class)->orderBy('happened_at');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function whatsappEvent()
    {
        return $this->belongsTo(WhatsappEvent::class, 'wa_event_id');
    }
}
