<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'user_id' => 'nullable|exists:users,id',
            'company_id' => 'required|exists:companies,id',
            'department_id' => 'required|exists:departments,id',
            'workplace_id' => 'nullable|exists:workplaces,id',
            'nom' => 'required|string|max:255',
            'cognoms' => 'required|string|max:255',
            'dni_nie' => 'required|string|max:255',
            'nss' => 'nullable|string|max:255',
            'data_naixement' => 'nullable|date',
            'email' => 'required|email',
            'telefon' => 'nullable|string|max:50',
            'politica_absencia_id' => 'nullable|exists:policy_absences,id',
            'politica_horari_id' => 'nullable|exists:policy_schedules,id',
            'torn_id' => 'nullable|exists:shifts,id',
            'percentatge_jornada' => 'nullable|numeric',
            'geoloc_requerida' => 'nullable|boolean',
            'whatsapp_phone_hash' => 'nullable|string',
            'whatsapp_verificat' => 'nullable|boolean',
            'actiu' => 'nullable|boolean',
            'data_alta' => 'required|date',
            'data_baixa' => 'nullable|date',
        ];
    }
}
