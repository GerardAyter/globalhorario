<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbsenceRequestUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|exists:employees,id',
            'absence_type_id' => 'nullable|exists:absence_types,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'working_days' => 'nullable|numeric',
            'half_day_start' => 'nullable|boolean',
            'half_day_end' => 'nullable|boolean',
            'employee_comment' => 'nullable|string',
            'manager_comment' => 'nullable|string',
            'attachment_url' => 'nullable|url',
            'status' => 'nullable|string',
        ];
    }
}
