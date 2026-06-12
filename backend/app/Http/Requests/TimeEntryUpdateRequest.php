<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimeEntryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|exists:users,id',
            'clock_in_at' => 'nullable|date_format:Y-m-d H:i:s',
            'clock_out_at' => 'nullable|date_format:Y-m-d H:i:s',
            'source' => 'nullable|string|max:100',
            'clock_in_geo' => 'nullable|array',
            'clock_out_geo' => 'nullable|array',
            'distance_meters' => 'nullable|integer',
            'within_radius' => 'nullable|boolean',
            'status' => 'nullable|in:pending,validated,rejected',
            'validated_by' => 'nullable|string',
            'validator_notes' => 'nullable|string',
            'integrity_hash' => 'nullable|string',
        ];
    }
}
