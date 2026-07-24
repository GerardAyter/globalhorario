<?php

namespace App\Http\Controllers\Api;

use App\Services\TimeEntryService;
use App\Http\Requests\TimeEntryStoreRequest;
use App\Http\Requests\TimeEntryUpdateRequest;
use Illuminate\Http\Request;

class TimeEntryController extends BaseController
{
    protected TimeEntryService $service;

    public function __construct(TimeEntryService $service)
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
        if (! $item) {
            return $this->error('Time entry not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(TimeEntryStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Time entry created', null, 201);
    }

    public function update(TimeEntryUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Time entry not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Time entry updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Time entry not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Time entry deleted', null, 204);
    }
}
