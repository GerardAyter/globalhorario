<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\TimeEntryBreak;
use App\Models\TimeEntryEditRequest;
use App\Models\TimeEntryLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeTrackingService
{
    public function getToday(User $user): array
    {
        $employee = $user->employee;
        if (! $employee) {
            return ['state' => 'no_employee', 'entry' => null, 'employee' => null, 'shift' => null];
        }

        $today = Carbon::today();
        $shift = $employee->shift;

        // Prioritza l'entrada activa (clocked_in o on_break)
        $entry = TimeEntry::with(['breaks', 'shift'])
            ->where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->whereIn('work_status', ['clocked_in', 'on_break'])
            ->first();

        // Si no n'hi ha d'activa, agafa la més recent del dia (ja tancada)
        if (! $entry) {
            $entry = TimeEntry::with(['breaks', 'shift'])
                ->where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->latest('clock_in_at')
                ->first();
        }

        if (! $entry) {
            return [
                'state'    => 'idle',
                'entry'    => null,
                'employee' => $this->formatEmployee($employee),
                'shift'    => $shift,
            ];
        }

        $state = $entry->work_status ?? 'clocked_out';

        $estimatedEnd = null;
        if ($entry->clock_in_at && $shift?->total_hours) {
            $estimatedEnd = $entry->clock_in_at->copy()->addMinutes((int) ($shift->total_hours * 60));
        }

        $currentBreak = $state === 'on_break'
            ? $entry->breaks->firstWhere('break_end_at', null)
            : null;

        $totalBreakMinutes = $entry->breaks
            ->whereNotNull('break_end_at')
            ->sum('duration_minutes');

        return [
            'state'    => $state,
            'entry'    => array_merge($entry->toArray(), [
                'estimated_end_at'    => $estimatedEnd?->toIso8601String(),
                'current_break'       => $currentBreak,
                'total_break_minutes' => (int) $totalBreakMinutes,
            ]),
            'employee' => $this->formatEmployee($employee),
            'shift'    => $shift,
        ];
    }

    public function clockIn(User $user, Request $request): array
    {
        $employee = $user->employee;
        if (! $employee) {
            throw new \Exception("No tens cap registre d'empleat associat.");
        }

        // Bloqueja només si hi ha una entrada ACTIVA (no tancada)
        $active = TimeEntry::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->whereIn('work_status', ['clocked_in', 'on_break'])
            ->exists();

        if ($active) {
            throw new \Exception('Ja tens un torn actiu. Finalitza\'l abans de començar-ne un de nou.');
        }

        $entry = TimeEntry::create([
            'tenant_id'   => $user->tenant_id,
            'user_id'     => $user->id,
            'employee_id' => $employee->id,
            'company_id'  => $employee->company_id,
            'shift_id'    => $employee->torn_id,
            'date'        => Carbon::today()->utc()->toDateString(),
            'clock_in_at' => now()->utc(),
            'work_status' => 'clocked_in',
            'origin'      => 'web',
            'status'      => 'pending',
        ]);

        $this->log($entry, 'clock_in', $user, $employee, $request);

        return $this->getToday($user);
    }

    public function clockOut(User $user, Request $request): array
    {
        $entry = $this->requireActiveEntry($user);

        // Tanca la pausa activa si n'hi ha
        if ($entry->work_status === 'on_break') {
            $openBreak = $entry->breaks()->whereNull('break_end_at')->first();
            if ($openBreak) {
                $nowUtc = now()->utc();
                $duration = (int) $openBreak->break_start_at->diffInMinutes($nowUtc);
                $openBreak->update(['break_end_at' => $nowUtc, 'duration_minutes' => $duration]);
                $this->log($entry, 'break_end', $user, $user->employee, $request, ['auto_closed' => true]);
            }
        }

        $nowUtc = now()->utc();

        $entry->update(['clock_out_at' => $nowUtc, 'work_status' => 'clocked_out']);
        $this->log($entry, 'clock_out', $user, $user->employee, $request);

        return $this->getToday($user);
    }

    public function breakStart(User $user, Request $request): array
    {
        $entry = $this->requireActiveEntry($user);

        if ($entry->work_status !== 'clocked_in') {
            throw new \Exception('Només es pot iniciar una pausa mentre estàs fitxat.');
        }

        $entry->update(['work_status' => 'on_break']);

        $nowUtc = now()->utc();

        TimeEntryBreak::create([
            'time_entry_id'  => $entry->id,
            'break_start_at' => $nowUtc,
        ]);

        $this->log($entry, 'break_start', $user, $user->employee, $request);

        return $this->getToday($user);
    }

    public function breakEnd(User $user, Request $request): array
    {
        $entry = $this->requireActiveEntry($user);

        if ($entry->work_status !== 'on_break') {
            throw new \Exception('No estàs en pausa.');
        }

        $openBreak = $entry->breaks()->whereNull('break_end_at')->first();
        if (! $openBreak) {
            throw new \Exception('No hi ha cap pausa activa.');
        }

        $nowUtc = now()->utc();
        $duration = (int) $openBreak->break_start_at->diffInMinutes($nowUtc);
        $openBreak->update(['break_end_at' => $nowUtc, 'duration_minutes' => $duration]);

        $entry->update(['work_status' => 'clocked_in']);

        $this->log($entry, 'break_end', $user, $user->employee, $request);

        return $this->getToday($user);
    }

    public function companyEntries(User $user, string $date): array
    {
        $companyId = $user->company_id ?? $user->employee?->company_id;
        if (! $companyId) return ['entries' => [], 'not_clocked' => []];

        $entries = TimeEntry::with(['employee.department', 'breaks'])
            ->where('company_id', $companyId)
            ->whereDate('date', $date)
            ->orderByDesc('clock_in_at')
            ->get()
            ->map(function (TimeEntry $e) {
                $totalBreak = $e->breaks->whereNotNull('break_end_at')->sum('duration_minutes');
                $effectiveMinutes = null;
                if ($e->clock_in_at && $e->clock_out_at) {
                    $effectiveMinutes = (int) $e->clock_in_at->diffInMinutes($e->clock_out_at) - $totalBreak;
                }
                return array_merge($e->toArray(), [
                    'total_break_minutes' => (int) $totalBreak,
                    'effective_minutes'   => $effectiveMinutes,
                ]);
            })
            ->values()
            ->toArray();

        // Empleats sense entrada (només per avui)
        $notClocked = [];
        if ($date === Carbon::today()->toDateString()) {
            $idsWithEntries = collect($entries)->pluck('employee_id')->filter()->unique()->values()->toArray();
            $notClocked = \App\Models\Employee::where('company_id', $companyId)
                ->where('actiu', true)
                ->whereNotIn('id', $idsWithEntries)
                ->with('department:id,name')
                ->orderBy('nom')
                ->get(['id', 'nom', 'cognoms', 'department_id'])
                ->toArray();
        }

        return ['entries' => $entries, 'not_clocked' => $notClocked];
    }

    public function myHistoryMonth(User $user, int $year, int $month): array
    {
        $employee = $user->employee;
        if (! $employee) return [];

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $entries = TimeEntry::with('breaks')
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('date')
            ->orderByDesc('clock_in_at')
            ->get();

        $entryIds = $entries->pluck('id');
        $breakIds = $entries->flatMap(fn($e) => $e->breaks->pluck('id'));

        try {
            $allEntryPending = TimeEntryEditRequest::where('status', 'pending')
                ->whereNull('break_id')
                ->whereIn('time_entry_id', $entryIds)
                ->get(['time_entry_id', 'type', 'initiated_by', 'id'])
                ->groupBy('time_entry_id');

            $allBreakPending = $breakIds->isNotEmpty()
                ? TimeEntryEditRequest::where('status', 'pending')
                    ->whereNotNull('break_id')
                    ->whereIn('break_id', $breakIds)
                    ->get(['break_id', 'type', 'initiated_by', 'id'])
                    ->groupBy('break_id')
                : collect();
        } catch (\Exception) {
            $allEntryPending = collect();
            $allBreakPending = collect();
        }

        return $entries->map(function (TimeEntry $e) use ($allEntryPending, $allBreakPending) {
                $totalBreak = $e->breaks->whereNotNull('break_end_at')->sum('duration_minutes');
                $effectiveMinutes = null;
                if ($e->clock_in_at && $e->clock_out_at) {
                    $effectiveMinutes = (int) $e->clock_in_at->diffInMinutes($e->clock_out_at) - $totalBreak;
                }
                $entryReqs = $allEntryPending->get($e->id, collect());
                $empReq    = $entryReqs->firstWhere('initiated_by', 'employee');
                $admReq    = $entryReqs->firstWhere('initiated_by', 'admin');

                return array_merge($e->toArray(), [
                    'clock_in_at'               => $e->clock_in_at?->toIso8601String(),
                    'clock_out_at'              => $e->clock_out_at?->toIso8601String(),
                    'total_break_minutes'       => (int) $totalBreak,
                    'effective_minutes'         => $effectiveMinutes,
                    'pending_request_type'      => $empReq?->type,
                    'pending_admin_request'     => $admReq ? ['type' => $admReq->type, 'id' => $admReq->id] : null,
                    'breaks'                    => $e->breaks->map(function ($b) use ($allBreakPending) {
                        $breakReqs = $allBreakPending->get($b->id, collect());
                        $empBreak  = $breakReqs->firstWhere('initiated_by', 'employee');
                        $admBreak  = $breakReqs->firstWhere('initiated_by', 'admin');
                        return [
                            'id'                        => $b->id,
                            'break_start_at'            => $b->break_start_at?->toIso8601String(),
                            'break_end_at'              => $b->break_end_at?->toIso8601String(),
                            'duration_minutes'          => $b->duration_minutes,
                            'pending_request_type'      => $empBreak?->type,
                            'pending_admin_request'     => $admBreak ? ['type' => $admBreak->type, 'id' => $admBreak->id] : null,
                        ];
                    })->values()->toArray(),
                ]);
            })
            ->values()
            ->toArray();
    }

    public function adminEntriesMonth(User $admin, int $year, int $month, ?int $employeeId): array
    {
        $companyId = $admin->company_id ?? $admin->employee?->company_id;
        if (! $companyId) return [];

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $query = TimeEntry::with('breaks', 'employee')
            ->whereHas('employee', fn($q) => $q->where('company_id', $companyId))
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('date')
            ->orderBy('employee_id')
            ->orderByDesc('clock_in_at');

        if ($employeeId) $query->where('employee_id', $employeeId);

        $entries = $query->get();

        $entryIds = $entries->pluck('id');
        $breakIds = $entries->flatMap(fn($e) => $e->breaks->pluck('id'));

        try {
            $allEntryPending = TimeEntryEditRequest::where('status', 'pending')
                ->whereNull('break_id')->whereIn('time_entry_id', $entryIds)
                ->get(['time_entry_id', 'type', 'initiated_by', 'id'])->groupBy('time_entry_id');

            $allBreakPending = $breakIds->isNotEmpty()
                ? TimeEntryEditRequest::where('status', 'pending')
                    ->whereNotNull('break_id')->whereIn('break_id', $breakIds)
                    ->get(['break_id', 'type', 'initiated_by', 'id'])->groupBy('break_id')
                : collect();
        } catch (\Exception) {
            $allEntryPending = collect();
            $allBreakPending = collect();
        }

        return $entries->map(function (TimeEntry $e) use ($allEntryPending, $allBreakPending) {
            $totalBreak       = $e->breaks->whereNotNull('break_end_at')->sum('duration_minutes');
            $effectiveMinutes = ($e->clock_in_at && $e->clock_out_at)
                ? (int) $e->clock_in_at->diffInMinutes($e->clock_out_at) - $totalBreak
                : null;

            $entryReqs = $allEntryPending->get($e->id, collect());
            $empReq    = $entryReqs->firstWhere('initiated_by', 'employee');
            $admReq    = $entryReqs->firstWhere('initiated_by', 'admin');

            return [
                'id'                    => $e->id,
                'date'                  => $e->date?->toDateString(),
                'clock_in_at'           => $e->clock_in_at?->toIso8601String(),
                'clock_out_at'          => $e->clock_out_at?->toIso8601String(),
                'work_status'           => $e->work_status,
                'total_break_minutes'   => (int) $totalBreak,
                'effective_minutes'     => $effectiveMinutes,
                'pending_request_type'  => $empReq?->type,
                'pending_admin_request' => $admReq ? ['type' => $admReq->type, 'id' => $admReq->id] : null,
                'employee'              => $e->employee ? [
                    'id'      => $e->employee->id,
                    'nom'     => $e->employee->nom,
                    'cognoms' => $e->employee->cognoms,
                ] : null,
                'breaks' => $e->breaks->map(function ($b) use ($allBreakPending) {
                    $breakReqs = $allBreakPending->get($b->id, collect());
                    $empBreak  = $breakReqs->firstWhere('initiated_by', 'employee');
                    $admBreak  = $breakReqs->firstWhere('initiated_by', 'admin');
                    return [
                        'id'                    => $b->id,
                        'break_start_at'        => $b->break_start_at?->toIso8601String(),
                        'break_end_at'          => $b->break_end_at?->toIso8601String(),
                        'duration_minutes'      => $b->duration_minutes,
                        'pending_request_type'  => $empBreak?->type,
                        'pending_admin_request' => $admBreak ? ['type' => $admBreak->type, 'id' => $admBreak->id] : null,
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();
    }

    public function deleteEntry(User $user, int $entryId): void
    {
        $employee = $user->employee;
        if (! $employee) throw new \Exception("No tens cap registre d'empleat associat.");

        $entry = TimeEntry::where('id', $entryId)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        TimeEntryLog::create([
            'time_entry_id' => $entry->id,
            'action'        => 'deleted',
            'happened_at'   => now(),
            'user_id'       => $user->id,
            'employee_id'   => $employee->id,
            'metadata'      => [
                'date'        => $entry->date?->toDateString(),
                'clock_in_at' => $entry->clock_in_at?->toIso8601String(),
            ],
            'created_at'    => now(),
        ]);

        $entry->delete();
    }

    public function myHistory(User $user, int $days = 30): array
    {
        $employee = $user->employee;
        if (! $employee) return [];

        return TimeEntry::with('breaks')
            ->where('employee_id', $employee->id)
            ->where('date', '>=', Carbon::today()->subDays($days))
            ->orderByDesc('clock_in_at')
            ->get()
            ->map(function (TimeEntry $e) {
                $totalBreak = $e->breaks->whereNotNull('break_end_at')->sum('duration_minutes');
                $effectiveMinutes = null;
                if ($e->clock_in_at && $e->clock_out_at) {
                    $effectiveMinutes = (int) $e->clock_in_at->diffInMinutes($e->clock_out_at) - $totalBreak;
                }
                return array_merge($e->toArray(), [
                    'total_break_minutes' => (int) $totalBreak,
                    'effective_minutes'   => $effectiveMinutes,
                ]);
            })
            ->values()
            ->toArray();
    }

    // ── Privat ────────────────────────────────────────────────────────────────

    private function requireActiveEntry(User $user): TimeEntry
    {
        $employee = $user->employee;
        if (! $employee) throw new \Exception("No tens cap registre d'empleat associat.");

        $entry = TimeEntry::with('breaks')
            ->where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->whereIn('work_status', ['clocked_in', 'on_break'])
            ->first();

        if (! $entry) throw new \Exception('No tens cap torn actiu avui.');

        return $entry;
    }

    private function log(TimeEntry $entry, string $action, User $user, ?Employee $employee, Request $request, array $metadata = []): void
    {
        TimeEntryLog::create([
            'time_entry_id' => $entry->id,
            'action'        => $action,
            'happened_at'   => now(),
            'user_id'       => $user->id,
            'employee_id'   => $employee?->id,
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'metadata'      => empty($metadata) ? null : $metadata,
            'created_at'    => now(),
        ]);
    }

    private function formatEmployee(Employee $employee): array
    {
        return [
            'id'      => $employee->id,
            'nom'     => $employee->nom,
            'cognoms' => $employee->cognoms,
        ];
    }
}
