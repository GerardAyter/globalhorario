<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    use HasFactory;

    protected $table = 'time_entries';

    protected $fillable = [
        'user_id',
        'clock_in_at',
        'clock_out_at',
        'source',
        'clock_in_geo',
        'clock_out_geo',
        'distance_meters',
        'within_radius',
        'status',
        'validated_by',
        'validator_notes',
        'integrity_hash',
    ];

    protected $casts = [
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
        'clock_in_geo' => 'array',
        'clock_out_geo' => 'array',
        'within_radius' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
