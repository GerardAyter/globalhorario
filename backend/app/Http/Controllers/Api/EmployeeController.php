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
        $data = $this->service->list($request->all());
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
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Employee created', null, 201);
    }

    public function update(EmployeeUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Employee not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Employee updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Employee not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Employee deleted', null, 204);
    }

    public function timeEntries($id)
    {
        $data = $this->service->timeEntries((int)$id);
        if ($data === null) {
            return $this->error('Employee not found', null, 404);
        }
        return $this->success($data);
    }
}
