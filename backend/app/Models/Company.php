<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Company extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'companies';

    protected $fillable = [
        'tenant_id',
        'name',
        'nom_legal',
        'tax_id',
        'adreca_facturacio',
        'telefon',
        'email_contacte',
        'persona_contacte',
        'logo_url',
        'favicon_url',
        'timezone',
        'country',
        'collective_agreement',
        'hr_configuration',
    ];

    protected $casts = [
        'hr_configuration' => 'array',
    ];

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function planFlags()
    {
        return $this->hasMany(CompanyPlanFlag::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
