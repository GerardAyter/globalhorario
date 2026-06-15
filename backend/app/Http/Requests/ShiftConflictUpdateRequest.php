<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftConflictUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|exists:employees,id',
            'assignment_a_id' => 'nullable|exists:shift_assignments,id',
            'assignment_b_id' => 'nullable|exists:shift_assignments,id',
            'type' => 'nullable|string',
            'resolution' => 'nullable|string',
            'resolution_note' => 'nullable|string',
        ];
    }
}
