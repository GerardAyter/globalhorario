<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkCalendar extends Model
{
    use HasFactory;

    protected $table = 'work_calendars';

    protected $fillable = [
        'company_id',
        'year',
        'national_holidays',
        'local_holidays',
        'annual_hours',
        'geographic_zone',
    ];

    protected $casts = [
        'national_holidays' => 'array',
        'local_holidays' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
