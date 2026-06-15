<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class PolicyAbsence extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'policy_absences';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'name',
        'vacation_days_per_year',
        'personal_days',
        'max_consecutive_days',
        'min_notice_days',
        'allow_accumulation',
        'max_accumulated_days',
        'approval_required',
        'approval_levels',
        'applies_to',
    ];

    protected $casts = [
        'allow_accumulation' => 'boolean',
        'approval_required' => 'boolean',
    ];
}
