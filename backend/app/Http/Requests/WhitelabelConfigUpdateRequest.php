<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WhitelabelConfigUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'sometimes|required|exists:tenants,id|unique:whitelabel_configs,tenant_id,'.$this->route('whitelabel_config'),
            'nom_producte' => 'nullable|string|max:255',
            'domini_custom' => 'nullable|string|max:255',
            'logo_url' => 'nullable|url',
            'favicon_url' => 'nullable|url',
            'color_primari' => 'nullable|string|max:50',
            'color_accent' => 'nullable|string|max:50',
            'email_remitent' => 'nullable|email',
            'peu_legal' => 'nullable|string',
            'idioma_defecte' => 'nullable|string|max:10',
            'ocult_powered_by' => 'nullable|boolean',
        ];
    }
}
