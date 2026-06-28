<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'sometimes|required|string|max:255',
            'location'   => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
        ];
    }
}
