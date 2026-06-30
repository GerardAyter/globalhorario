<?php

namespace App\Http\Controllers\Api;

use App\Services\VacationBalanceService;
use Illuminate\Http\Request;

class VacationBalanceController extends BaseController
{
    public function __construct(private VacationBalanceService $service) {}

    /** Balanç propi de l'usuari autenticat */
    public function my(Request $request)
    {
        return $this->success($this->service->myBalance($request->user()));
    }

    /** Balanços de tots els empleats de l'empresa (HR+) */
    public function index(Request $request)
    {
        return $this->success($this->service->companyBalances($request->user()));
    }

    public function show(Request $request, int $id)
    {
        $item = \App\Models\VacationBalance::find($id);
        if (! $item) return $this->error('Balanç no trobat', null, 404);
        return $this->success($item);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'       => 'required|exists:employees,id',
            'year'              => 'required|integer|min:2000|max:2099',
            'generated_days'    => 'required|numeric|min:0',
            'carried_from_previous' => 'nullable|numeric|min:0',
            'manual_adjustment' => 'nullable|numeric',
            'adjustment_reason' => 'nullable|string|max:500',
            'personal_days_total' => 'nullable|integer|min:0',
        ]);

        $employee = \App\Models\Employee::find($data['employee_id']);
        $data['user_id']    = $employee->user_id;
        $data['company_id'] = $employee->company_id;

        $item = \App\Models\VacationBalance::create($data);
        return $this->success($item, 'Balanç creat', null, 201);
    }

    public function update(Request $request, int $id)
    {
        $item = \App\Models\VacationBalance::find($id);
        if (! $item) return $this->error('Balanç no trobat', null, 404);

        $data = $request->validate([
            'generated_days'      => 'sometimes|numeric|min:0',
            'carried_from_previous' => 'sometimes|numeric|min:0',
            'manual_adjustment'   => 'sometimes|numeric',
            'adjustment_reason'   => 'nullable|string|max:500',
            'personal_days_total' => 'sometimes|integer|min:0',
        ]);

        $item->update($data);
        return $this->success($item, 'Balanç actualitzat');
    }

    public function destroy(int $id)
    {
        $item = \App\Models\VacationBalance::find($id);
        if (! $item) return $this->error('Balanç no trobat', null, 404);
        $item->delete();
        return $this->success(null, 'Balanç eliminat');
    }
}
