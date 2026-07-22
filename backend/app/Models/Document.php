<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Document extends Model
{
    use HasTenant;

    const TYPE_PAYROLL            = 'payroll';
    const TYPE_MEDICAL_CERTIFICATE = 'medical_certificate';
    const TYPE_OTHER              = 'other';

    const TYPES = [self::TYPE_PAYROLL, self::TYPE_MEDICAL_CERTIFICATE, self::TYPE_OTHER];

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'uploaded_by',
        'title',
        'description',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
