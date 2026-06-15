<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbsenceTypeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'category' => 'nullable|string',
            'requires_document' => 'nullable|boolean',
            'paid' => 'nullable|boolean',
            'max_days_per_year' => 'nullable|numeric',
            'counts_for_seniority' => 'nullable|boolean',
            'legal_basis' => 'nullable|string',
            'calendar_color' => 'nullable|string',
            'visible_to_company' => 'nullable|boolean',
        ];
    }
}
