<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PolicyAbsenceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'company_id' => 'nullable|exists:companies,id',
            'name' => 'required|string|max:255',
            'vacation_days_per_year' => 'nullable|numeric',
            'personal_days' => 'nullable|numeric',
            'max_consecutive_days' => 'nullable|integer',
            'min_notice_days' => 'nullable|integer',
            'allow_accumulation' => 'nullable|boolean',
            'max_accumulated_days' => 'nullable|numeric',
            'approval_required' => 'nullable|boolean',
            'approval_levels' => 'nullable|integer',
            'applies_to' => 'nullable|string',
        ];
    }
}
