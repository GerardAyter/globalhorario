<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VacationBalanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|exists:employees,id',
            'year' => 'nullable|integer',
            'generated_days' => 'nullable|numeric',
            'taken_days' => 'nullable|numeric',
            'pending_days' => 'nullable|numeric',
            'carried_from_previous' => 'nullable|numeric',
            'expiry_date_carried' => 'nullable|date',
            'manual_adjustment' => 'nullable|numeric',
            'adjustment_reason' => 'nullable|string',
        ];
    }
}
