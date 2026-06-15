<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class PlanFeatureFlag extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'plan_feature_flags';

    protected $fillable = [
        'tenant_id',
        'feature',
        'actiu',
        'config_extra',
        'data_expiracio',
    ];

    protected $casts = [
        'actiu' => 'boolean',
        'config_extra' => 'array',
        'data_expiracio' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
