<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Notification extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'notifications_custom';

    protected $fillable = [
        'tenant_id',
        'recipient_id',
        'employee_id',
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

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
