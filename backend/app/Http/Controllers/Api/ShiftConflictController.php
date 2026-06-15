<?php

namespace App\Http\Controllers\Api;

use App\Services\ShiftConflictService;
use App\Http\Requests\ShiftConflictStoreRequest;
use App\Http\Requests\ShiftConflictUpdateRequest;
use Illuminate\Http\Request;

class ShiftConflictController extends BaseController
{
    protected ShiftConflictService $service;

    public function __construct(ShiftConflictService $service)
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
            return $this->error('Shift conflict not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(ShiftConflictStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Shift conflict created', null, 201);
    }

    public function update(ShiftConflictUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Shift conflict not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Shift conflict updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Shift conflict not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Shift conflict deleted', null, 204);
    }

    public function resolve(Request $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Shift conflict not found', null, 404);
        }
        $data = $request->validate(['resolution' => 'required|string', 'resolution_note' => 'nullable|string']);
        $item = $this->service->resolve($item, $data);
        return $this->success($item, 'Shift conflict resolved');
    }
}
