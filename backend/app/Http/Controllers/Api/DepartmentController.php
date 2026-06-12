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
        $data = $this->service->list($request->all());
        return $this->success($data);
    }

    public function show($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Department not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(DepartmentStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Department created', null, 201);
    }

    public function update(DepartmentUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Department not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Department updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Department not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Department deleted', null, 204);
    }
}
