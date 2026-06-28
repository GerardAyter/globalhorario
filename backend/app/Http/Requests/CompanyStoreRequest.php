<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                 => 'required|string|max:255',
            'nom_legal'            => ['nullable', 'string', 'max:255', Rule::unique('companies', 'nom_legal')],
            'tax_id'               => ['nullable', 'string', 'max:100', Rule::unique('companies', 'tax_id')],
            'adreca_facturacio'    => 'nullable|string|max:500',
            'telefon'              => 'nullable|string|max:30',
            'email_contacte'       => 'nullable|email|max:255',
            'persona_contacte'     => 'nullable|string|max:255',
            'timezone'             => 'nullable|string|max:100',
            'country'              => 'nullable|string|max:100',
            'collective_agreement' => 'nullable|string|max:255',
            'hr_configuration'     => 'nullable|array',
            'logo_base64'          => 'nullable|string',
            'favicon_base64'       => 'nullable|string',
            'modules'              => 'nullable|array',
            'modules.*'            => 'string|in:time_tracking,documents,calendar',
            'admin_name'           => 'nullable|string|max:255',
            'admin_email'          => 'nullable|email|max:255|unique:users,email',
            'admin_password'       => 'nullable|string|min:8|required_with:admin_email',
        ];
    }
}
