<?php

namespace App\Http\Controllers\Api;

use App\Services\WorkplaceService;
use App\Http\Requests\WorkplaceStoreRequest;
use App\Http\Requests\WorkplaceUpdateRequest;
use Illuminate\Http\Request;

class WorkplaceController extends BaseController
{
    protected WorkplaceService $service;

    public function __construct(WorkplaceService $service)
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
            return $this->error('Workplace not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(WorkplaceStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Workplace created', null, 201);
    }

    public function update(WorkplaceUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Workplace not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Workplace updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Workplace not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Workplace deleted', null, 204);
    }
}
