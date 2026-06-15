<?php

namespace App\Http\Controllers\Api;

use App\Services\PolicyAbsenceService;
use App\Http\Requests\PolicyAbsenceStoreRequest;
use App\Http\Requests\PolicyAbsenceUpdateRequest;
use Illuminate\Http\Request;

class PolicyAbsenceController extends BaseController
{
    protected PolicyAbsenceService $service;

    public function __construct(PolicyAbsenceService $service)
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
            return $this->error('Policy absence not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(PolicyAbsenceStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Policy absence created', null, 201);
    }

    public function update(PolicyAbsenceUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Policy absence not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Policy absence updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Policy absence not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Policy absence deleted', null, 204);
    }
}
