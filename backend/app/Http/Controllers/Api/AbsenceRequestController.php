<?php

namespace App\Http\Controllers\Api;

use App\Services\AbsenceRequestService;
use Illuminate\Http\Request;

class AbsenceRequestController extends BaseController
{
    public function __construct(private AbsenceRequestService $service) {}

    /** Les meves sol·licituds (usuari autenticat) */
    public function my(Request $request)
    {
        return $this->success($this->service->myRequests($request->user()));
    }

    /** Totes les sol·licituds de l'empresa (HR+) */
    public function index(Request $request)
    {
        $status = $request->query('status');
        return $this->success($this->service->companyRequests($request->user(), $status));
    }

    /** Crear nova sol·licitud */
    public function store(Request $request)
    {
        $data = $request->validate([
            'absence_type_id'  => 'required|exists:absence_types,id',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'half_day_start'   => 'boolean',
            'half_day_end'     => 'boolean',
            'employee_comment' => 'nullable|string|max:1000',
        ]);

        try {
            $item = $this->service->storeForUser($request->user(), $data);
            return $this->success($item, 'Sol·licitud creada correctament', null, 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function show(Request $request, int $id)
    {
        $item = $this->service->myRequests($request->user())->firstWhere('id', $id);
        if (! $item) return $this->error('Sol·licitud no trobada', null, 404);
        return $this->success($item);
    }

    /** Cancel·lar (propietari) */
    public function destroy(Request $request, int $id)
    {
        $item = $this->service->myRequests($request->user())->firstWhere('id', $id);
        if (! $item) return $this->error('Sol·licitud no trobada', null, 404);

        try {
            $this->service->cancel($item);
            return $this->success(null, 'Sol·licitud cancel·lada');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    /** Aprovar (HR+) */
    public function approve(Request $request, int $id)
    {
        $item = \App\Models\AbsenceRequest::find($id);
        if (! $item) return $this->error('Sol·licitud no trobada', null, 404);

        $comment = $request->input('manager_comment') ?? '';
        try {
            $item = $this->service->approve($item, $request->user(), $comment);
            return $this->success($item, 'Sol·licitud aprovada');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    /** Denegar (HR+) */
    public function deny(Request $request, int $id)
    {
        $item = \App\Models\AbsenceRequest::find($id);
        if (! $item) return $this->error('Sol·licitud no trobada', null, 404);

        $comment = $request->input('manager_comment') ?? '';
        try {
            $item = $this->service->deny($item, $request->user(), $comment);
            return $this->success($item, 'Sol·licitud denegada');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }
}
