<?php

namespace App\Http\Controllers\Api;

use App\Services\ContractService;
use App\Http\Requests\ContractStoreRequest;
use App\Http\Requests\ContractUpdateRequest;
use Illuminate\Http\Request;

class ContractController extends BaseController
{
    protected ContractService $service;

    public function __construct(ContractService $service)
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
            return $this->error('Contract not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(ContractStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Contract created', null, 201);
    }

    public function update(ContractUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Contract not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Contract updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Contract not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Contract deleted', null, 204);
    }
}
