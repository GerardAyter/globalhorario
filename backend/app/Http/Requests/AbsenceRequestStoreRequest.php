<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbsenceRequestStoreRequest extends FormRequest
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
            'absence_type_id' => 'required|exists:absence_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'working_days' => 'nullable|numeric',
            'half_day_start' => 'nullable|boolean',
            'half_day_end' => 'nullable|boolean',
            'employee_comment' => 'nullable|string',
            'attachment_url' => 'nullable|url',
        ];
    }
}
