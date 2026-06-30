<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class VacationBalance extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'vacation_balances';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_id',
        'company_id',
        'year',
        'generated_days',
        'taken_days',
        'pending_days',
        'personal_days_total',
        'carried_from_previous',
        'expiry_date_carried',
        'manual_adjustment',
        'adjustment_reason',
    ];

    protected $casts = [
        'generated_days' => 'decimal:2',
        'taken_days' => 'decimal:2',
        'pending_days' => 'decimal:2',
        'carried_from_previous' => 'decimal:2',
        'manual_adjustment' => 'decimal:2',
        'expiry_date_carried' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
