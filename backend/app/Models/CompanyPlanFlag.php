<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class CompanyPlanFlag extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'company_plan_flags';

    protected $fillable = ['tenant_id', 'company_id', 'feature', 'actiu'];

    protected $casts = ['actiu' => 'boolean'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
