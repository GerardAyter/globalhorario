<?php

namespace App\Http\Controllers\Api;

use App\Services\TenantService;
use App\Http\Requests\TenantStoreRequest;
use App\Http\Requests\TenantUpdateRequest;
use Illuminate\Http\Request;

class TenantController extends BaseController
{
    protected TenantService $service;

    public function __construct(TenantService $service)
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
            return $this->error('Tenant not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(TenantStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Tenant created', null, 201);
    }

    public function update(TenantUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Tenant not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Tenant updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Tenant not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Tenant deleted', null, 204);
    }
}
