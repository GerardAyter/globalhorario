<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'           => 'sometimes|required|string|max:255',
            'color'          => 'nullable|string|max:20',
            'days_of_week'   => 'nullable|array',
            'days_of_week.*' => 'integer|between:1,7',
            'start_time'     => 'nullable|date_format:H:i',
            'total_hours'    => 'nullable|numeric|min:0',
            'active'         => 'nullable|boolean',
            'flexible_entry' => 'nullable|boolean',
            'flex_entry_from' => 'nullable|date_format:H:i',
            'flex_entry_to'   => 'nullable|date_format:H:i',
            'break_duration'  => 'nullable|integer|min:0',
            'break_from'      => 'nullable|date_format:H:i',
            'break_to'        => 'nullable|date_format:H:i',
        ];
    }
}
