<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WhitelabelConfigStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id|unique:whitelabel_configs,tenant_id',
            'nom_producte' => 'required|string|max:255',
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
