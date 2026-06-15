<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom_intern' => 'sometimes|required|string|unique:tenants,nom_intern,'.$this->route('tenant'),
            'pla' => 'nullable|in:starter,pro,enterprise',
            'max_empleats' => 'nullable|integer',
            'actiu' => 'nullable|boolean',
            'data_alta' => 'nullable|date',
            'data_baixa' => 'nullable|date',
        ];
    }
}
