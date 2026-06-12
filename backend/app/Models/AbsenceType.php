<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsenceType extends Model
{
    use HasFactory;

    protected $table = 'absence_types';

    protected $fillable = [
        'name',
        'company_id',
        'category',
        'requires_document',
        'paid',
        'max_days_per_year',
        'counts_for_seniority',
        'legal_basis',
        'calendar_color',
        'visible_to_company',
    ];

    protected $casts = [
        'requires_document' => 'boolean',
        'paid' => 'boolean',
        'counts_for_seniority' => 'boolean',
        'visible_to_company' => 'boolean',
    ];
}
