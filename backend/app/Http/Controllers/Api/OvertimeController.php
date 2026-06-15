<?php

namespace App\Http\Controllers\Api;

use App\Services\OvertimeService;
use App\Http\Requests\OvertimeStoreRequest;
use App\Http\Requests\OvertimeUpdateRequest;
use Illuminate\Http\Request;

class OvertimeController extends BaseController
{
    protected OvertimeService $service;

    public function __construct(OvertimeService $service)
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
            return $this->error('Overtime not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(OvertimeStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Overtime created', null, 201);
    }

    public function update(OvertimeUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Overtime not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Overtime updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Overtime not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Overtime deleted', null, 204);
    }

    public function approve($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Overtime not found', null, 404);
        }
        $item = $this->service->approve($item);
        return $this->success($item, 'Overtime approved');
    }
}
