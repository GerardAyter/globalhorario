<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class WhatsappSession extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'whatsapp_sessions';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'wa_phone_hash',
        'estat',
        'codi_verificacio',
        'codi_expira_en',
        'verificada_en',
        'wa_contact_name',
        'darrer_missatge_en',
    ];

    protected $casts = [
        'codi_expira_en' => 'datetime',
        'verificada_en' => 'datetime',
        'darrer_missatge_en' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
