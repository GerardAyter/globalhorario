<?php

namespace App\Http\Controllers\Api;

use App\Services\WhitelabelConfigService;
use App\Http\Requests\WhitelabelConfigStoreRequest;
use App\Http\Requests\WhitelabelConfigUpdateRequest;
use Illuminate\Http\Request;

class WhitelabelConfigController extends BaseController
{
    protected WhitelabelConfigService $service;

    public function __construct(WhitelabelConfigService $service)
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
            return $this->error('Whitelabel config not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(WhitelabelConfigStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Whitelabel config created', null, 201);
    }

    public function update(WhitelabelConfigUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Whitelabel config not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Whitelabel config updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Whitelabel config not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Whitelabel config deleted', null, 204);
    }
}
