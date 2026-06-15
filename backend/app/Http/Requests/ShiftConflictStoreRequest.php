<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftConflictStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'employee_id' => 'nullable|exists:employees,id',
            'assignment_a_id' => 'required|exists:shift_assignments,id',
            'assignment_b_id' => 'required|exists:shift_assignments,id',
            'type' => 'nullable|string',
            'detected_at' => 'nullable|date',
        ];
    }
}
