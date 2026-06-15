<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Workplace extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'workplaces';

    protected $fillable = [
        'tenant_id',
        'title',
        'department_id',
        'professional_category',
        'contribution_group',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
