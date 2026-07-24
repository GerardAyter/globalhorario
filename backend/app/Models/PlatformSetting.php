<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $table = 'platform_settings';

    protected $fillable = [
        'nom_producte',
        'logo_url',
        'favicon_url',
        'color_primari',
        'email_suport',
        'peu_legal',
    ];
}
