<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Break extends Model
{
    use HasFactory;

    protected $table = 'breaks';

    protected $fillable = [
        'time_entry_id',
        'start_at',
        'end_at',
        'type',
        'minutes_counted',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'minutes_counted' => 'boolean',
    ];

    public function timeEntry()
    {
        return $this->belongsTo(TimeEntry::class);
    }
}
