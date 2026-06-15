<?php

namespace App\Http\Controllers\Api;

use App\Services\OvertimePolicyService;
use App\Http\Requests\OvertimePolicyStoreRequest;
use App\Http\Requests\OvertimePolicyUpdateRequest;
use Illuminate\Http\Request;

class OvertimePolicyController extends BaseController
{
    protected OvertimePolicyService $service;

    public function __construct(OvertimePolicyService $service)
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
            return $this->error('Overtime policy not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(OvertimePolicyStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Overtime policy created', null, 201);
    }

    public function update(OvertimePolicyUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Overtime policy not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Overtime policy updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Overtime policy not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Overtime policy deleted', null, 204);
    }
}
