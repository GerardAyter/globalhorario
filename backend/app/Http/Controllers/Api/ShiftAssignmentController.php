<?php

namespace App\Http\Controllers\Api;

use App\Services\ShiftAssignmentService;
use App\Http\Requests\ShiftAssignmentStoreRequest;
use App\Http\Requests\ShiftAssignmentUpdateRequest;
use Illuminate\Http\Request;

class ShiftAssignmentController extends BaseController
{
    protected ShiftAssignmentService $service;

    public function __construct(ShiftAssignmentService $service)
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
            return $this->error('Shift assignment not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(ShiftAssignmentStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Shift assignment created', null, 201);
    }

    public function update(ShiftAssignmentUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Shift assignment not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Shift assignment updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Shift assignment not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Shift assignment deleted', null, 204);
    }
}
