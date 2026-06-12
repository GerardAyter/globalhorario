<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicySchedule extends Model
{
    use HasFactory;

    protected $table = 'policy_schedules';

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'tolerance_minutes',
        'require_geolocation',
        'geolocation_radius_meters',
        'allow_remote_clocking',
        'max_hours_per_day',
        'min_rest_between_shifts',
        'require_approval_for_records',
        'auto_approve_if',
    ];

    protected $casts = [
        'require_geolocation' => 'boolean',
        'allow_remote_clocking' => 'boolean',
        'require_approval_for_records' => 'boolean',
    ];
}
