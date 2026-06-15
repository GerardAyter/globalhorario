<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftAssignmentStoreRequest extends FormRequest
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
            'shift_id' => 'required|exists:shifts,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'origin' => 'nullable|string',
            'status' => 'nullable|string',
            'priority' => 'nullable|integer',
            'note' => 'nullable|string',
        ];
    }
}
