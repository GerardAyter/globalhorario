<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OvertimeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|exists:employees,id',
            'overtime_policy_id' => 'nullable|exists:overtime_policies,id',
            'date' => 'nullable|date',
            'hours' => 'nullable|numeric',
            'compensation_type' => 'nullable|string',
            'status' => 'nullable|string',
        ];
    }
}
