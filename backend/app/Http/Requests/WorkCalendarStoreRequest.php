<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkCalendarStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'company_id' => 'required|exists:companies,id',
            'year' => 'required|integer',
            'national_holidays' => 'nullable|array',
            'local_holidays' => 'nullable|array',
            'annual_hours' => 'nullable|numeric',
            'geographic_zone' => 'nullable|string',
        ];
    }
}
