<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|exists:employees,id',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer',
            'ordinary_hours' => 'nullable|numeric',
            'gross_salary' => 'nullable|numeric',
            'tax_withholding' => 'nullable|numeric',
            'net_salary' => 'nullable|numeric',
            'status' => 'nullable|string',
        ];
    }
}
