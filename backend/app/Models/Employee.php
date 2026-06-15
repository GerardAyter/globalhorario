<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Employee extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'employees';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'company_id',
        'department_id',
        'workplace_id',
        'nom',
        'cognoms',
        'dni_nie',
        'nss',
        'data_naixement',
        'email',
        'telefon',
        'politica_absencia_id',
        'politica_horari_id',
        'torn_id',
        'percentatge_jornada',
        'geoloc_requerida',
        'whatsapp_phone_hash',
        'whatsapp_verificat',
        'actiu',
        'data_alta',
        'data_baixa',
    ];

    protected $casts = [
        'data_naixement' => 'date',
        'percentatge_jornada' => 'decimal:2',
        'geoloc_requerida' => 'boolean',
        'whatsapp_verificat' => 'boolean',
        'actiu' => 'boolean',
        'data_alta' => 'date',
        'data_baixa' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function workplace()
    {
        return $this->belongsTo(Workplace::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }
}
