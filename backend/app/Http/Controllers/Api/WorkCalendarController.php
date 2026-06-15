<?php

namespace App\Http\Controllers\Api;

use App\Services\WorkCalendarService;
use App\Http\Requests\WorkCalendarStoreRequest;
use App\Http\Requests\WorkCalendarUpdateRequest;
use Illuminate\Http\Request;

class WorkCalendarController extends BaseController
{
    protected WorkCalendarService $service;

    public function __construct(WorkCalendarService $service)
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
            return $this->error('Work calendar not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(WorkCalendarStoreRequest $request)
    {
        $item = $this->service->create($request->validated());
        return $this->success($item, 'Work calendar created', null, 201);
    }

    public function update(WorkCalendarUpdateRequest $request, $id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Work calendar not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Work calendar updated');
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Work calendar not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Work calendar deleted', null, 204);
    }
}
