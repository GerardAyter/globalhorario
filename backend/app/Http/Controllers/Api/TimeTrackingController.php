<?php

namespace App\Http\Controllers\Api;

use App\Services\TimeTrackingService;
use Illuminate\Http\Request;

class TimeTrackingController extends BaseController
{
    public function __construct(private TimeTrackingService $service) {}

    public function today(Request $request)
    {
        return $this->success($this->service->getToday($request->user()));
    }

    public function clockIn(Request $request)
    {
        try {
            $data = $this->service->clockIn($request->user(), $request);
            return $this->success($data, 'Entrada registrada', null, 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function clockOut(Request $request)
    {
        try {
            $data = $this->service->clockOut($request->user(), $request);
            return $this->success($data, 'Torn finalitzat');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function breakStart(Request $request)
    {
        try {
            $data = $this->service->breakStart($request->user(), $request);
            return $this->success($data, 'Pausa iniciada');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function breakEnd(Request $request)
    {
        try {
            $data = $this->service->breakEnd($request->user(), $request);
            return $this->success($data, 'Pausa finalitzada');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function myHistory(Request $request)
    {
        $days = (int) $request->query('days', 30);
        return $this->success($this->service->myHistory($request->user(), $days));
    }

    public function adminEntriesMonth(Request $request)
    {
        $year       = (int) $request->query('year', now()->year);
        $month      = (int) $request->query('month', now()->month);
        $employeeId = $request->query('employee_id') ? (int) $request->query('employee_id') : null;
        $data = $this->service->adminEntriesMonth($request->user(), $year, $month, $employeeId);
        return $this->success($data);
    }

    public function myHistoryMonth(Request $request)
    {
        $year  = (int) $request->query('year',  now()->year);
        $month = (int) $request->query('month', now()->month);
        return $this->success($this->service->myHistoryMonth($request->user(), $year, $month));
    }

    public function deleteEntry(Request $request, int $id)
    {
        try {
            $this->service->deleteEntry($request->user(), $id);
            return $this->success(null, 'Fitxatge eliminat.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    public function companyEntries(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        return $this->success($this->service->companyEntries($request->user(), $date));
    }
}
