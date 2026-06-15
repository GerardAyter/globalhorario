<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $table = 'tenants';

    protected $fillable = [
        'nom_intern',
        'pla',
        'max_empleats',
        'actiu',
        'data_alta',
        'data_baixa',
    ];

    protected $casts = [
        'actiu' => 'boolean',
        'data_alta' => 'datetime',
        'data_baixa' => 'datetime',
    ];

    public function whitelabel()
    {
        return $this->hasOne(WhitelabelConfig::class);
    }
}
