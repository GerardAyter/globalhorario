<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class ShiftConflict extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'shift_conflicts';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_id',
        'assignment_a_id',
        'assignment_b_id',
        'type',
        'detected_at',
        'resolution',
        'resolution_note',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
