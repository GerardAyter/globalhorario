<?php

namespace App\Http\Controllers\Api;

use App\Models\Conveni;
use Illuminate\Http\Request;

class ConveniController extends BaseController
{
    /** Llista convenis de l'empresa (Admin+) */
    public function index(Request $request)
    {
        $user      = $request->user();
        $companyId = $user->company_id ?? $user->employee?->company_id;

        if (! $companyId) return $this->success([]);

        $convenis = Conveni::where('company_id', $companyId)
            ->withCount('employees')
            ->orderBy('name')
            ->get();

        return $this->success($convenis);
    }

    /** Crear conveni (Admin+) */
    public function store(Request $request)
    {
        $user      = $request->user();
        $companyId = $user->company_id ?? $user->employee?->company_id;

        if (! $companyId) return $this->error('Empresa no trobada', null, 422);

        $data = $request->validate([
            'name'                  => 'required|string|max:120',
            'weekly_hours'          => 'required|numeric|min:1|max:168',
            'break_minutes'         => 'nullable|integer|min:0|max:240',
            'weekly_overtime_max'   => 'nullable|numeric|min:0',
            'monthly_overtime_max'  => 'nullable|numeric|min:0',
            'annual_overtime_max'   => 'nullable|numeric|min:0',
            'vacation_days'         => 'required|integer|min:0',
            'personal_days'         => 'required|integer|min:0',
        ]);

        $conveni = Conveni::create([
            'company_id' => $companyId,
            ...$data,
        ]);

        $conveni->loadCount('employees');

        return $this->success($conveni, 'Conveni creat correctament', null, 201);
    }

    /** Actualitzar conveni (Admin+) */
    public function update(Request $request, int $id)
    {
        $conveni = Conveni::find($id);
        if (! $conveni) return $this->error('Conveni no trobat', null, 404);

        $data = $request->validate([
            'name'                  => 'sometimes|string|max:120',
            'weekly_hours'          => 'sometimes|numeric|min:1|max:168',
            'break_minutes'         => 'nullable|integer|min:0|max:240',
            'weekly_overtime_max'   => 'nullable|numeric|min:0',
            'monthly_overtime_max'  => 'nullable|numeric|min:0',
            'annual_overtime_max'   => 'nullable|numeric|min:0',
            'vacation_days'         => 'sometimes|integer|min:0',
            'personal_days'         => 'sometimes|integer|min:0',
        ]);

        $conveni->update($data);
        $conveni->loadCount('employees');

        return $this->success($conveni, 'Conveni actualitzat');
    }

    /** Eliminar conveni (Admin+) */
    public function destroy(int $id)
    {
        $conveni = Conveni::withCount('employees')->find($id);
        if (! $conveni) return $this->error('Conveni no trobat', null, 404);

        if ($conveni->employees_count > 0) {
            return $this->error(
                "No es pot eliminar: {$conveni->employees_count} empleat(s) assignats a aquest conveni.",
                null, 422
            );
        }

        $conveni->delete();

        return $this->success(null, 'Conveni eliminat');
    }
}
