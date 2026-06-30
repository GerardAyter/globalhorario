<?php

namespace App\Http\Controllers\Api;

use App\Models\TimeEntry;
use App\Models\TimeEntryBreak;
use App\Models\TimeEntryEditRequest;
use App\Services\TimeEntryEditRequestService;
use Illuminate\Http\Request;

class TimeEntryEditRequestController extends BaseController
{
    public function __construct(private TimeEntryEditRequestService $service) {}

    public function store(Request $request, int $id)
    {
        $entry = TimeEntry::findOrFail($id);

        $employee = $request->user()->employee;
        if (! $employee || $entry->employee_id !== $employee->id) {
            return $this->error('Accés denegat.', null, 403);
        }

        $validated = $request->validate([
            'clock_in_at'  => 'nullable|date_format:Y-m-d\TH:i',
            'clock_out_at' => 'nullable|date_format:Y-m-d\TH:i',
            'reason'       => 'required|string|max:500',
        ]);

        $reason = $validated['reason'];
        $requestedData = array_filter([
            'clock_in_at'  => $validated['clock_in_at'] ?? null,
            'clock_out_at' => $validated['clock_out_at'] ?? null,
        ], fn($v) => $v !== null);

        try {
            $req = $this->service->createRequest($request->user(), $entry, $requestedData, $reason);
            return $this->success($req->toArray(), 'Sol·licitud enviada correctament.', null, 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function storeDelete(Request $request, int $id)
    {
        $entry = TimeEntry::findOrFail($id);

        $employee = $request->user()->employee;
        if (! $employee || $entry->employee_id !== $employee->id) {
            return $this->error('Accés denegat.', null, 403);
        }

        $request->validate(['reason' => 'required|string|max:500']);

        try {
            $req = $this->service->createDeleteRequest($request->user(), $entry, $request->reason);
            return $this->success($req->toArray(), 'Sol·licitud d\'eliminació enviada.', null, 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function storeBreakEdit(Request $request, int $breakId)
    {
        $break = TimeEntryBreak::findOrFail($breakId);
        $entry = $break->timeEntry;

        $employee = $request->user()->employee;
        if (! $employee || $entry->employee_id !== $employee->id) {
            return $this->error('Accés denegat.', null, 403);
        }

        $validated = $request->validate([
            'break_start_at' => 'nullable|date_format:Y-m-d\TH:i',
            'break_end_at'   => 'nullable|date_format:Y-m-d\TH:i',
            'reason'         => 'required|string|max:500',
        ]);

        $requestedData = array_filter([
            'break_start_at' => $validated['break_start_at'] ?? null,
            'break_end_at'   => $validated['break_end_at'] ?? null,
        ], fn($v) => $v !== null);

        try {
            $req = $this->service->createBreakEditRequest($request->user(), $break, $requestedData, $validated['reason']);
            return $this->success($req->toArray(), 'Sol·licitud enviada correctament.', null, 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function storeBreakDelete(Request $request, int $breakId)
    {
        $break = TimeEntryBreak::findOrFail($breakId);
        $entry = $break->timeEntry;

        $employee = $request->user()->employee;
        if (! $employee || $entry->employee_id !== $employee->id) {
            return $this->error('Accés denegat.', null, 403);
        }

        $request->validate(['reason' => 'required|string|max:500']);

        try {
            $req = $this->service->createBreakDeleteRequest($request->user(), $break, $request->reason);
            return $this->success($req->toArray(), 'Sol·licitud d\'eliminació de pausa enviada.', null, 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    // ── Admin crea sol·licituds per empleats ────────────────────────────────────

    public function storeAdminEntryEdit(Request $request, int $id)
    {
        $entry = TimeEntry::findOrFail($id);
        $validated = $request->validate([
            'clock_in_at'  => 'nullable|date_format:Y-m-d\TH:i',
            'clock_out_at' => 'nullable|date_format:Y-m-d\TH:i',
            'reason'       => 'required|string|max:500',
        ]);
        $requestedData = array_filter(['clock_in_at' => $validated['clock_in_at'] ?? null, 'clock_out_at' => $validated['clock_out_at'] ?? null], fn($v) => $v !== null);
        try {
            $req = $this->service->createAdminEntryEdit($request->user(), $entry, $requestedData, $validated['reason']);
            return $this->success($req->toArray(), 'Sol·licitud enviada a l\'empleat.', null, 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function storeAdminEntryDelete(Request $request, int $id)
    {
        $entry = TimeEntry::findOrFail($id);
        $request->validate(['reason' => 'required|string|max:500']);
        try {
            $req = $this->service->createAdminEntryDelete($request->user(), $entry, $request->reason);
            return $this->success($req->toArray(), 'Sol·licitud d\'eliminació enviada a l\'empleat.', null, 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function storeAdminBreakEdit(Request $request, int $breakId)
    {
        $break = TimeEntryBreak::findOrFail($breakId);
        $validated = $request->validate([
            'break_start_at' => 'nullable|date_format:Y-m-d\TH:i',
            'break_end_at'   => 'nullable|date_format:Y-m-d\TH:i',
            'reason'         => 'required|string|max:500',
        ]);
        $requestedData = array_filter(['break_start_at' => $validated['break_start_at'] ?? null, 'break_end_at' => $validated['break_end_at'] ?? null], fn($v) => $v !== null);
        try {
            $req = $this->service->createAdminBreakEdit($request->user(), $break, $requestedData, $validated['reason']);
            return $this->success($req->toArray(), 'Sol·licitud enviada a l\'empleat.', null, 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function storeAdminBreakDelete(Request $request, int $breakId)
    {
        $break = TimeEntryBreak::findOrFail($breakId);
        $request->validate(['reason' => 'required|string|max:500']);
        try {
            $req = $this->service->createAdminBreakDelete($request->user(), $break, $request->reason);
            return $this->success($req->toArray(), 'Sol·licitud enviada a l\'empleat.', null, 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    // ── Empleat revisa sol·licituds admin ────────────────────────────────────

    public function myIncoming(Request $request)
    {
        $data = $this->service->pendingIncomingForEmployee($request->user());
        return $this->success($data->values());
    }

    public function employeeApprove(Request $request, int $id)
    {
        $req = TimeEntryEditRequest::findOrFail($id);
        try {
            $this->service->approveAsEmployee($request->user(), $req, $request->input('note'));
            return $this->success(null, 'Canvi aprovat.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function employeeDeny(Request $request, int $id)
    {
        $req = TimeEntryEditRequest::findOrFail($id);
        try {
            $this->service->denyAsEmployee($request->user(), $req, $request->input('note'));
            return $this->success(null, 'Canvi denegat.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function pendingCount(Request $request)
    {
        $count = $this->service->pendingCountForCompany($request->user());
        return $this->success(['count' => $count]);
    }

    public function index(Request $request)
    {
        $all = $request->boolean('all', false);
        $data = $all
            ? $this->service->allForCompany($request->user())
            : $this->service->pendingForCompany($request->user());
        return $this->success($data->values());
    }

    public function approve(Request $request, int $id)
    {
        $req = TimeEntryEditRequest::findOrFail($id);

        try {
            $this->service->approve($request->user(), $req, $request->input('note'));
            return $this->success(null, 'Sol·licitud aprovada.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function deny(Request $request, int $id)
    {
        $req = TimeEntryEditRequest::findOrFail($id);

        try {
            $this->service->deny($request->user(), $req, $request->input('note'));
            return $this->success(null, 'Sol·licitud denegada.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }
}
