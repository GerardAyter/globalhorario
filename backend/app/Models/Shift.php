<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';

    protected $fillable = [
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
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'crosses_midnight' => 'boolean',
        'active' => 'boolean',
    ];
}
