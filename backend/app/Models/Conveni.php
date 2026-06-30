<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Conveni extends Model
{
    use HasTenant;

    protected $fillable = [
        'company_id',
        'tenant_id',
        'name',
        'weekly_hours',
        'break_minutes',
        'weekly_overtime_max',
        'monthly_overtime_max',
        'annual_overtime_max',
        'vacation_days',
        'personal_days',
    ];

    protected $casts = [
        'weekly_hours'          => 'decimal:2',
        'weekly_overtime_max'   => 'decimal:2',
        'monthly_overtime_max'  => 'decimal:2',
        'annual_overtime_max'   => 'decimal:2',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
