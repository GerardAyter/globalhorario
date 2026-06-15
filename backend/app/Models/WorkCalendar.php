<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class WorkCalendar extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'work_calendars';

    protected $fillable = [
        'tenant_id',
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

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
