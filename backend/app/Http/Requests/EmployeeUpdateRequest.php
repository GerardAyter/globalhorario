<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'company_id' => 'nullable|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',
            'workplace_id' => 'nullable|exists:workplaces,id',
            'nom' => 'nullable|string|max:255',
            'cognoms' => 'nullable|string|max:255',
            'dni_nie' => 'nullable|string|max:255',
            'nss' => 'nullable|string|max:255',
            'data_naixement' => 'nullable|date',
            'email' => 'nullable|email',
            'telefon' => 'nullable|string|max:50',
            'politica_absencia_id' => 'nullable|exists:policy_absences,id',
            'politica_horari_id'   => 'nullable|exists:policy_schedules,id',
            'conveni_id'           => 'nullable|exists:convenis,id',
            'torn_id'              => 'nullable|exists:shifts,id',
            'percentatge_jornada' => 'nullable|numeric',
            'geoloc_requerida' => 'nullable|boolean',
            'whatsapp_phone_hash' => 'nullable|string',
            'whatsapp_verificat' => 'nullable|boolean',
            'actiu' => 'nullable|boolean',
            'data_alta' => 'nullable|date',
            'data_baixa' => 'nullable|date',
        ];
    }
}
