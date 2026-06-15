<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class ShiftAssignment extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'shift_assignments';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_id',
        'shift_id',
        'start_date',
        'end_date',
        'origin',
        'status',
        'priority',
        'note',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
