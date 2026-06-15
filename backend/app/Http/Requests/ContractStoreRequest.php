<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractStoreRequest extends FormRequest
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
            'type' => 'nullable|string',
            'work_time' => 'nullable|string',
            'weekly_hours' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'annual_gross_salary' => 'nullable|numeric',
            'tax_percentage' => 'nullable|numeric',
            'active' => 'nullable|boolean',
        ];
    }
}
