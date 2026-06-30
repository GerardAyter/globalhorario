<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Shift extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'shifts';

    protected $fillable = [
        'tenant_id',
        'name',
        'company_id',
        'color',
        'type',
        'start_time',
        'end_time',
        'crosses_midnight',
        'days_of_week',
        'total_hours',
        'min_rest_after',
        'location_required',
        'active',
        'flexible_entry',
        'flex_entry_from',
        'flex_entry_to',
        'break_duration',
        'break_from',
        'break_to',
    ];

    protected $casts = [
        'days_of_week'    => 'array',
        'crosses_midnight' => 'boolean',
        'active'          => 'boolean',
        'flexible_entry'  => 'boolean',
    ];
}
