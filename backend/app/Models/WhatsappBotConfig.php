<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class WhatsappBotConfig extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'whatsapp_bot_configs';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'waba_id',
        'phone_number_id',
        'webhook_verify_token',
        'actiu',
        'idioma_bot',
        'missatge_benvinguda',
        'missatge_entrada_ok',
        'missatge_sortida_ok',
        'missatge_error_fora_radi',
    ];
}
