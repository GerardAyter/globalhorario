<?php

namespace App\Http\Controllers\Api;

use App\Services\EmployeeService;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use Illuminate\Http\Request;

class EmployeeController extends BaseController
{
    protected EmployeeService $service;

    public function __construct(EmployeeService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user      = $request->user();
        $companyId = $user->company_id ?? $user->employee?->company_id;
        $filters   = $companyId ? ['company_id' => $companyId] : [];

        $data = $this->service->list($filters);
        return $this->success($data);
    }

    public function show($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Employee not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(EmployeeStoreRequest $request)
    {
        $user      = $request->user();
        $companyId = $user->company_id ?? $user->employee?->company_id;

        if (! $companyId) {
            return $this->error("El vostre compte no té cap empresa associada", null, 403);
        }

        $data               = $request->validated();
        $data['company_id'] = $companyId;

        $item = $this->service->create($data);
        return $this->success($this->service->find($item->id), 'Employee created', null, 201);
    }

    public function update(EmployeeUpdateRequest $request, int $id)
    {
        $item = $this->service->find($id);
        if (! $item) {
            return $this->error('Employee not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Employee updated');
    }

    public function destroy(int $id)
    {
        $item = $this->service->find($id);
        if (! $item) {
            return $this->error('Employee not found', null, 404);
        }

        if ($item->user && $item->user->role === 'admin') {
            return $this->error("No es pot eliminar l'empleat administrador de l'empresa", null, 403);
        }

        $this->service->delete($item);
        return $this->success(null, 'Employee deleted', null, 204);
    }

    public function timeEntries(int $id)
    {
        $data = $this->service->timeEntries((int)$id);
        if ($data === null) {
            return $this->error('Employee not found', null, 404);
        }
        return $this->success($data);
    }
}
