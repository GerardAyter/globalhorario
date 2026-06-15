<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class WhitelabelConfig extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'whitelabel_configs';

    protected $fillable = [
        'tenant_id',
        'nom_producte',
        'domini_custom',
        'logo_url',
        'favicon_url',
        'color_primari',
        'color_accent',
        'email_remitent',
        'peu_legal',
        'idioma_defecte',
        'ocult_powered_by',
    ];

    protected $casts = [
        'ocult_powered_by' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
