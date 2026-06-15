<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Overtime extends Model
{
    use HasFactory;
    use HasTenant;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_DENIED = 'denied';
    const STATUS_CANCELLED = 'cancelled';

    const COMPENSATION_TYPE_PAID = 'paid';
    const COMPENSATION_TYPE_TIME_OFF = 'time_off';

    protected $table = 'overtimes';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_id',
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

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
