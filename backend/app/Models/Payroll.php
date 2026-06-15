<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Payroll extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'payrolls';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_id',
        'month',
        'year',
        'ordinary_hours',
        'overtime_rem_pay',
        'gross_salary',
        'tax_withholding',
        'employee_social_security',
        'employer_social_security',
        'net_salary',
        'paid_days_paid_leave',
        'sick_days',
        'status',
        'pdf_url',
    ];

    protected $casts = [
        'ordinary_hours' => 'decimal:2',
        'overtime_rem_pay' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'tax_withholding' => 'decimal:2',
        'employee_social_security' => 'decimal:2',
        'employer_social_security' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
