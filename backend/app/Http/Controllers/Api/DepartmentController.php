<?php

namespace App\Http\Controllers\Api;

use App\Services\DepartmentService;
use App\Http\Requests\DepartmentStoreRequest;
use App\Http\Requests\DepartmentUpdateRequest;
use Illuminate\Http\Request;

class DepartmentController extends BaseController
{
    protected DepartmentService $service;

    public function __construct(DepartmentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user      = $request->user();
        $companyId = $user->company_id ?? $user->employee?->company_id;
        $filters   = $request->all();
        if ($companyId) $filters['company_id'] = $companyId;

        return $this->success($this->service->list($filters));
    }

    public function show(int $id)
    {
        $item = $this->service->find($id);
        if (! $item) {
            return $this->error('Department not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(DepartmentStoreRequest $request)
    {
        $user      = $request->user();
        $companyId = $user->company_id ?? $user->employee?->company_id;

        if (! $companyId) {
            return $this->error('El vostre compte no té cap empresa assignada', null, 403);
        }

        $data               = $request->validated();
        $data['company_id'] = $companyId;

        return $this->success($this->service->create($data), 'Department created', null, 201);
    }

    public function update(DepartmentUpdateRequest $request, int $id)
    {
        $item = $this->service->find($id);
        if (! $item) {
            return $this->error('Department not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Department updated');
    }

    public function destroy(int $id)
    {
        $item = $this->service->find($id);
        if (! $item) {
            return $this->error('Department not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Department deleted', null, 204);
    }
}
