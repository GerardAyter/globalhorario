<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom_intern'          => ['required', 'string', 'max:255', Rule::unique('tenants', 'nom_intern')],
            'nom_legal'           => ['nullable', 'string', 'max:255', Rule::unique('tenants', 'nom_legal')],
            'nif'                 => ['nullable', 'string', 'max:20',  Rule::unique('tenants', 'nif')],
            'adreca_facturacio'   => 'nullable|string|max:500',
            'telefon'             => 'nullable|string|max:30',
            'email_contacte'      => 'nullable|email|max:255',
            'persona_contacte'    => 'nullable|string|max:255',
            'pla'                 => 'required|in:starter,pro,enterprise',
            'max_empleats'        => 'nullable|integer|min:0',
            'actiu'               => 'nullable|boolean',
            'data_alta'           => 'nullable|date',
            'data_baixa'          => 'nullable|date',
            'logo_base64'         => 'nullable|string',
            'favicon_base64'      => 'nullable|string',
            'modules'             => 'nullable|array',
            'modules.*'           => 'string|in:time_tracking,documents,calendar',
            'superadmin_name'     => 'nullable|string|max:255',
            'superadmin_email'    => 'nullable|email|max:255|unique:users,email',
            'superadmin_password' => 'nullable|string|min:8|required_with:superadmin_email',
        ];
    }
}
