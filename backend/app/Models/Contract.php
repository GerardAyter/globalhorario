<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $table = 'contracts';

    protected $fillable = [
        'user_id',
        'type',
        'work_time',
        'weekly_hours',
        'start_date',
        'end_date',
        'annual_gross_salary',
        'tax_percentage',
        'active',
        'termination_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'weekly_hours' => 'decimal:2',
        'annual_gross_salary' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
