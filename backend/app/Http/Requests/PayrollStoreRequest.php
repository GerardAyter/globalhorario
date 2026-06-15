<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollStoreRequest extends FormRequest
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
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
            'ordinary_hours' => 'nullable|numeric',
            'gross_salary' => 'nullable|numeric',
            'tax_withholding' => 'nullable|numeric',
            'net_salary' => 'nullable|numeric',
            'status' => 'nullable|string',
        ];
    }
}
