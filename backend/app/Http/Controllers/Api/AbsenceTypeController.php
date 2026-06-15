<?php

namespace App\Http\Controllers\Api;

use App\Services\AbsenceTypeService;
use App\Http\Requests\AbsenceTypeStoreRequest;
use App\Http\Requests\AbsenceTypeUpdateRequest;
use Illuminate\Http\Request;

class AbsenceTypeController extends BaseController
{
    protected AbsenceTypeService $service;

    public function __construct(AbsenceTypeService $service)
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
            return $this->error('Absence type not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(AbsenceTypeStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Absence type created', null, 201);
    }

    public function update(AbsenceTypeUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Absence type not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Absence type updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Absence type not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Absence type deleted', null, 204);
    }
}
