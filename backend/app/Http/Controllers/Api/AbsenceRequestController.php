<?php

namespace App\Http\Controllers\Api;

use App\Services\AbsenceRequestService;
use App\Http\Requests\AbsenceRequestStoreRequest;
use App\Http\Requests\AbsenceRequestUpdateRequest;
use Illuminate\Http\Request;

class AbsenceRequestController extends BaseController
{
    protected AbsenceRequestService $service;

    public function __construct(AbsenceRequestService $service)
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
            return $this->error('Absence request not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(AbsenceRequestStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Absence request created', null, 201);
    }

    public function update(AbsenceRequestUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Absence request not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Absence request updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Absence request not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Absence request deleted', null, 204);
    }

    public function approve($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Absence request not found', null, 404);
        }
        $item = $this->service->approve($item);
        return $this->success($item, 'Absence request approved');
    }

    public function deny($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Absence request not found', null, 404);
        }
        $item = $this->service->deny($item);
        return $this->success($item, 'Absence request denied');
    }
}
