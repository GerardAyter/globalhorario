<?php

namespace App\Http\Controllers\Api;

use App\Services\PolicyScheduleService;
use App\Http\Requests\PolicyScheduleStoreRequest;
use App\Http\Requests\PolicyScheduleUpdateRequest;
use Illuminate\Http\Request;

class PolicyScheduleController extends BaseController
{
    protected PolicyScheduleService $service;

    public function __construct(PolicyScheduleService $service)
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
            return $this->error('Policy schedule not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(PolicyScheduleStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Policy schedule created', null, 201);
    }

    public function update(PolicyScheduleUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Policy schedule not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Policy schedule updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Policy schedule not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Policy schedule deleted', null, 204);
    }
}
