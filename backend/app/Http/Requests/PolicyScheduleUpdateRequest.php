<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PolicyScheduleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => 'nullable|exists:companies,id',
            'name' => 'sometimes|required|string|max:255',
            'type' => 'nullable|string',
            'tolerance_minutes' => 'nullable|integer',
            'require_geolocation' => 'nullable|boolean',
            'geolocation_radius_meters' => 'nullable|integer',
            'allow_remote_clocking' => 'nullable|boolean',
            'max_hours_per_day' => 'nullable|numeric',
            'min_rest_between_shifts' => 'nullable|integer',
            'require_approval_for_records' => 'nullable|boolean',
            'auto_approve_if' => 'nullable|string',
        ];
    }
}
