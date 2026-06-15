<?php

namespace App\Http\Controllers\Api;

use App\Services\PayrollService;
use App\Http\Requests\PayrollStoreRequest;
use App\Http\Requests\PayrollUpdateRequest;
use Illuminate\Http\Request;

class PayrollController extends BaseController
{
    protected PayrollService $service;

    public function __construct(PayrollService $service)
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
            return $this->error('Payroll not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(PayrollStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Payroll created', null, 201);
    }

    public function update(PayrollUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Payroll not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Payroll updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Payroll not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Payroll deleted', null, 204);
    }
}
