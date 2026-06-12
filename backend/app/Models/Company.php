<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'tax_id',
        'timezone',
        'country',
        'collective_agreement',
        'hr_configuration',
    ];

    protected $casts = [
        'hr_configuration' => 'array',
    ];

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}
