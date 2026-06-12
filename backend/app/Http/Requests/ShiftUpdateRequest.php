<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'company_id' => 'sometimes|required|exists:companies,id',
            'color' => 'nullable|string|max:20',
            'type' => 'nullable|in:fixed,rotating,flexible',
            'start_time' => 'nullable|date_format:H:i:s',
            'end_time' => 'nullable|date_format:H:i:s',
            'crosses_midnight' => 'nullable|boolean',
            'days_of_week' => 'nullable|array',
            'total_hours' => 'nullable|numeric',
            'min_rest_after' => 'nullable|integer',
            'location_required' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
        ];
    }
}
