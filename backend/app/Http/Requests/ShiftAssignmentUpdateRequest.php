<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftAssignmentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|exists:employees,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'origin' => 'nullable|string',
            'status' => 'nullable|string',
            'priority' => 'nullable|integer',
            'note' => 'nullable|string',
        ];
    }
}
