<?php

namespace App\Http\Controllers\Api;

use App\Services\ShiftService;
use App\Http\Requests\ShiftStoreRequest;
use App\Http\Requests\ShiftUpdateRequest;
use Illuminate\Http\Request;

class ShiftController extends BaseController
{
    protected ShiftService $service;

    public function __construct(ShiftService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user      = $request->user();
        $companyId = $user->company_id ?? $user->employee?->company_id;
        $data      = $this->service->list($companyId ? ['company_id' => $companyId] : []);
        return $this->success($data);
    }

    public function show($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) return $this->error('Shift not found', null, 404);
        return $this->success($item);
    }

    public function store(ShiftStoreRequest $request)
    {
        $user      = $request->user();
        $companyId = $user->company_id ?? $user->employee?->company_id;
        if (! $companyId) return $this->error('El vostre compte no té cap empresa associada', null, 403);

        $data               = $request->validated();
        $data['company_id'] = $companyId;

        $item = $this->service->create($data);
        return $this->success($item, 'Shift created', null, 201);
    }

    public function update(ShiftUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) return $this->error('Shift not found', null, 404);
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Shift updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) return $this->error('Shift not found', null, 404);
        $this->service->delete($item);
        return $this->success(null, 'Shift deleted', null, 204);
    }
}
