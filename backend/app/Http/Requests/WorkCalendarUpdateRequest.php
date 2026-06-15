<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkCalendarUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => 'nullable|exists:companies,id',
            'year' => 'nullable|integer',
            'national_holidays' => 'nullable|array',
            'local_holidays' => 'nullable|array',
            'annual_hours' => 'nullable|numeric',
            'geographic_zone' => 'nullable|string',
        ];
    }
}
