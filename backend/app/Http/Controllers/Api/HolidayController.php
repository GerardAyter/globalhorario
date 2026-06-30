<?php

namespace App\Http\Controllers\Api;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends BaseController
{
    /** Tots els festius de l'empresa (qualsevol usuari autenticat) */
    public function index(Request $request)
    {
        $user      = $request->user();
        $companyId = $user->company_id ?? $user->employee?->company_id;

        if (! $companyId) return $this->success([]);

        $year      = $request->query('year', now()->year);
        $holidays  = Holiday::where('company_id', $companyId)
            ->where(function ($q) use ($year) {
                // Festius de l'any exacte o recurrents (qualsevol any)
                $q->whereYear('date', $year)->orWhere('recurring', true);
            })
            ->orderBy('date')
            ->get();

        return $this->success($holidays);
    }

    /** Crear festiu (Admin+) */
    public function store(Request $request)
    {
        $user      = $request->user();
        $companyId = $user->company_id ?? $user->employee?->company_id;

        $data = $request->validate([
            'name'      => 'required|string|max:120',
            'date'      => 'required|date',
            'type'      => 'required|in:national,local,company',
            'color'     => 'nullable|string|max:20',
            'recurring' => 'boolean',
        ]);

        $holiday = Holiday::create([
            'company_id' => $companyId,
            'name'       => $data['name'],
            'date'       => $data['date'],
            'type'       => $data['type'],
            'color'      => $data['color'] ?? '#EF4444',
            'recurring'  => $data['recurring'] ?? false,
        ]);

        return $this->success($holiday, 'Festiu creat correctament', null, 201);
    }

    /** Actualitzar festiu (Admin+) */
    public function update(Request $request, int $id)
    {
        $holiday = Holiday::find($id);
        if (! $holiday) return $this->error('Festiu no trobat', null, 404);

        $data = $request->validate([
            'name'      => 'sometimes|string|max:120',
            'date'      => 'sometimes|date',
            'type'      => 'sometimes|in:national,local,company',
            'color'     => 'nullable|string|max:20',
            'recurring' => 'boolean',
        ]);

        $holiday->update($data);

        return $this->success($holiday, 'Festiu actualitzat');
    }

    /** Eliminar festiu (Admin+) */
    public function destroy(int $id)
    {
        $holiday = Holiday::find($id);
        if (! $holiday) return $this->error('Festiu no trobat', null, 404);

        $holiday->delete();

        return $this->success(null, 'Festiu eliminat');
    }
}
