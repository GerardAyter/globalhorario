<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OvertimePolicyStoreRequest extends FormRequest
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
            'annual_limit' => 'nullable|numeric',
            'weekly_limit' => 'nullable|numeric',
            'compensation' => 'nullable|string',
            'remuneration_multiplier' => 'nullable|numeric',
            'days_comp_per_hour' => 'nullable|numeric',
            'comp_expiry_days' => 'nullable|integer',
            'approval_required' => 'nullable|boolean',
            'notify_limit_percent' => 'nullable|numeric',
        ];
    }
}
