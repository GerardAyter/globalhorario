<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OvertimeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'employee_id' => 'required|exists:employees,id',
            'overtime_policy_id' => 'nullable|exists:overtime_policies,id',
            'date' => 'required|date',
            'hours' => 'required|numeric',
            'compensation_type' => 'nullable|string',
            'status' => 'nullable|string',
        ];
    }
}
