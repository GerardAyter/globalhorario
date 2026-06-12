<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workplace extends Model
{
    use HasFactory;

    protected $table = 'workplaces';

    protected $fillable = [
        'title',
        'department_id',
        'professional_category',
        'contribution_group',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
