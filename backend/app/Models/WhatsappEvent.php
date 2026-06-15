<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class WhatsappEvent extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'whatsapp_events';

    public $timestamps = false; // only created_at

    protected $fillable = [
        'tenant_id',
        'sessio_id',
        'direccio',
        'tipus_msg',
        'payload_raw',
        'intencio',
        'geoloc_lat',
        'geoloc_lng',
        'time_entry_id',
        'estat_proc',
        'error_msg',
        'created_at',
    ];

    protected $casts = [
        'payload_raw' => 'array',
        'geoloc_lat' => 'decimal:7',
        'geoloc_lng' => 'decimal:7',
        'created_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(WhatsappSession::class, 'sessio_id');
    }
}
