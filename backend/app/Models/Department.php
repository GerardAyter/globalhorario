<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Department extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'departments';

    protected $fillable = [
        'tenant_id',
        'name',
        'company_id',
        'manager_id',
        'location',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
