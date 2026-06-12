<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'collective_agreement' => 'nullable|string|max:255',
            'hr_configuration' => 'nullable|array',
        ];
    }
}
