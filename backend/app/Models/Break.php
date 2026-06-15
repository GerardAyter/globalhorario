<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Break extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'breaks';

    protected $fillable = [
        'tenant_id',
        'time_entry_id',
        'employee_id',
        'start_at',
        'end_at',
        'type',
        'minutes_counted',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'minutes_counted' => 'boolean',
    ];

    public function timeEntry()
    {
        return $this->belongsTo(TimeEntry::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
