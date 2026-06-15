<?php

namespace App\Http\Controllers\Api;

use App\Services\VacationBalanceService;
use App\Http\Requests\VacationBalanceStoreRequest;
use App\Http\Requests\VacationBalanceUpdateRequest;
use Illuminate\Http\Request;

class VacationBalanceController extends BaseController
{
    protected VacationBalanceService $service;

    public function __construct(VacationBalanceService $service)
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
            return $this->error('Vacation balance not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(VacationBalanceStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Vacation balance created', null, 201);
    }

    public function update(VacationBalanceUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Vacation balance not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Vacation balance updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Vacation balance not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Vacation balance deleted', null, 204);
    }
}
