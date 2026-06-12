<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimePolicy extends Model
{
    use HasFactory;

    protected $table = 'overtime_policies';

    protected $fillable = [
        'company_id',
        'name',
        'annual_limit',
        'weekly_limit',
        'compensation',
        'remuneration_multiplier',
        'days_comp_per_hour',
        'comp_expiry_days',
        'approval_required',
        'notify_limit_percent',
    ];

    protected $casts = [
        'approval_required' => 'boolean',
    ];
}
