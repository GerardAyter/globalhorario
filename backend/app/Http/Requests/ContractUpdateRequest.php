<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|exists:employees,id',
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
