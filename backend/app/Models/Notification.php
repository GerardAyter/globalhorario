<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications_custom';

    protected $fillable = [
        'recipient_id',
        'type',
        'message',
        'channel',
        'read',
        'entity_reference',
        'reference_id',
    ];

    protected $casts = [
        'read' => 'boolean',
    ];

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
