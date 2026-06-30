<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Holiday extends Model
{
    use HasTenant;

    protected $fillable = [
        'company_id', 'tenant_id', 'name', 'date', 'type', 'color', 'recurring',
    ];

    protected $casts = [
        'date'      => 'date',
        'recurring' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
