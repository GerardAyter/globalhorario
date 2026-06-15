<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Reseller extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'resellers';

    protected $fillable = [
        'tenant_id',
        'nom',
        'contacte',
        'comissio_pct',
    ];

    protected $casts = [
        'comissio_pct' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
