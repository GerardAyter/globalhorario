<?php

namespace App\Services;

use App\Models\AbsenceRequest;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\User;
use App\Models\VacationBalance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AbsenceRequestService extends BaseService
{
    public function __construct(private NotificationService $notifications) {}
    // ── Lectura ────────────────────────────────────────────────────────────────

    /** Requests de l'usuari autenticat */
    public function myRequests(User $user): Collection
    {
        return AbsenceRequest::with(['type', 'employee'])
            ->where('user_id', $user->id)
            ->orderByDesc('start_date')
            ->get();
    }

    /** Totes les requests de l'empresa (HR+) */
    public function companyRequests(User $user, ?string $status = null): Collection
    {
        $companyId = $user->company_id ?? $user->employee?->company_id;

        $q = AbsenceRequest::with(['type', 'employee.department', 'user'])
            ->where('company_id', $companyId)
            ->orderByDesc('start_date');

        if ($status) {
            $q->where('status', $status);
        }

        return $q->get();
    }

    // ── Escriptura ─────────────────────────────────────────────────────────────

    /** Crea una sol·licitud per a l'usuari autenticat */
    public function storeForUser(User $user, array $data): AbsenceRequest
    {
        $employee  = $user->employee;
        $startDate = Carbon::parse($data['start_date']);
        $endDate   = Carbon::parse($data['end_date']);

        if ($endDate->lt($startDate)) {
            throw new \Exception('La data de fi no pot ser anterior a la d\'inici.');
        }

        // Validar comentari obligatori
        $type = \App\Models\AbsenceType::find($data['absence_type_id']);
        if ($type?->requires_comment && empty(trim($data['employee_comment'] ?? ''))) {
            throw new \Exception('El comentari és obligatori per a aquest tipus d\'absència.');
        }

        $companyId   = $employee?->company_id ?? $user->company_id;
        $workingDays = $this->countWorkingDays($startDate, $endDate, $companyId);
        if (isset($data['half_day_start']) && $data['half_day_start']) $workingDays -= 0.5;
        if (isset($data['half_day_end'])   && $data['half_day_end'])   $workingDays -= 0.5;
        $workingDays = max(0.5, $workingDays);

        $request = AbsenceRequest::create([
            'user_id'          => $user->id,
            'employee_id'      => $employee?->id,
            'company_id'       => $companyId,
            'absence_type_id'  => $data['absence_type_id'],
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'working_days'     => $workingDays,
            'half_day_start'   => $data['half_day_start'] ?? false,
            'half_day_end'     => $data['half_day_end'] ?? false,
            'status'           => AbsenceRequest::STATUS_PENDING,
            'employee_comment' => $data['employee_comment'] ?? null,
        ]);

        // Afegir als dies pendents del balanç (vacances / personals)
        if ($employee) {
            $this->adjustBalance($request, 'pending', +$workingDays);
        }

        $request->load(['type', 'employee']);

        // Notificar tots els admins de l'empresa
        $this->notifyAdmins($request, $user);

        return $request;
    }

    /** Cancel·la una sol·licitud (propietari o HR+) */
    public function cancel(AbsenceRequest $request): AbsenceRequest
    {
        if ($request->status === AbsenceRequest::STATUS_APPROVED) {
            // Retornar dies gaudits
            $this->adjustBalance($request, 'taken', -$request->working_days);
        } elseif ($request->status === AbsenceRequest::STATUS_PENDING) {
            $this->adjustBalance($request, 'pending', -$request->working_days);
        }

        $request->update(['status' => AbsenceRequest::STATUS_CANCELLED]);
        return $request;
    }

    // ── Aprovació / Denegació (HR+) ────────────────────────────────────────────

    public function approve(AbsenceRequest $request, User $approver, string $comment = ''): AbsenceRequest
    {
        if ($request->status !== AbsenceRequest::STATUS_PENDING) {
            throw new \Exception('Només es poden aprovar sol·licituds pendents.');
        }

        // Mou dies: de pending a taken
        $this->adjustBalance($request, 'pending', -$request->working_days);
        $this->adjustBalance($request, 'taken',   +$request->working_days);

        $request->update([
            'status'           => AbsenceRequest::STATUS_APPROVED,
            'manager_comment'  => $comment ?: null,
        ]);

        $request->load(['type', 'employee.department', 'user']);
        $this->notifyRequester($request, 'approved');
        return $request;
    }

    public function deny(AbsenceRequest $request, User $approver, string $comment = ''): AbsenceRequest
    {
        if ($request->status !== AbsenceRequest::STATUS_PENDING) {
            throw new \Exception('Només es poden denegar sol·licituds pendents.');
        }

        if (empty(trim($comment))) {
            throw new \Exception('Cal afegir un comentari per denegar una sol·licitud.');
        }

        // Alliberar dies pendents
        $this->adjustBalance($request, 'pending', -$request->working_days);

        $request->update([
            'status'          => AbsenceRequest::STATUS_DENIED,
            'manager_comment' => $comment ?: null,
        ]);

        $request->load(['type', 'employee.department', 'user']);
        $this->notifyRequester($request, 'denied');
        return $request;
    }

    // ── Privat ─────────────────────────────────────────────────────────────────

    private function notifyAdmins(AbsenceRequest $request, User $requester): void
    {
        $companyId = $request->company_id
            ?? $requester->company_id
            ?? $requester->employee?->company_id;
        if (! $companyId) return;

        $emp  = $request->employee;
        $name = $emp ? "{$emp->nom} {$emp->cognoms}" : ($requester->name ?? 'Un usuari');
        $days = (float) $request->working_days;
        $type = $request->type?->name ?? 'Absència';

        $admins = User::where(function ($q) use ($companyId) {
            $q->where('company_id', $companyId)
              ->orWhereHas('employee', fn ($q2) => $q2->where('company_id', $companyId));
        })->whereIn('role', ['hr', 'admin', 'superadmin'])
          ->where('id', '!=', $requester->id)
          ->get();

        foreach ($admins as $admin) {
            $this->notifications->send(
                $admin->id,
                'absence_requested',
                "Nova sol·licitud de {$name}",
                "{$type} · {$days} " . ($days === 1.0 ? 'dia' : 'dies'),
                ['request_id' => $request->id, 'url' => "/absences?request={$request->id}"]
            );
        }
    }

    private function notifyRequester(AbsenceRequest $request, string $status): void
    {
        $label = $status === 'approved' ? 'aprovada ✓' : 'denegada';
        $type  = $request->type?->name ?? 'Sol·licitud';
        $body  = $request->manager_comment
            ? "\"{$request->manager_comment}\""
            : null;

        $this->notifications->send(
            $request->user_id,
            "absence_{$status}",
            "{$type} {$label}",
            $body,
            ['request_id' => $request->id, 'url' => "/absences?request={$request->id}"]
        );
    }

    private function adjustBalance(AbsenceRequest $request, string $field, float $delta): void
    {
        $employeeId = $request->employee_id;
        if (! $employeeId) return;

        $category = $request->type?->category ?? 'other';
        // Vacances i personals es descompten del balanç
        if (! in_array($category, ['vacation', 'personal'])) return;

        $balance = VacationBalance::firstOrCreate(
            ['user_id' => $request->user_id, 'year' => now()->year],
            [
                'employee_id'     => $employeeId,
                'company_id'      => $request->company_id,
                'generated_days'  => 0,
                'taken_days'      => 0,
                'pending_days'    => 0,
            ]
        );

        $col = $field === 'pending' ? 'pending_days' : 'taken_days';
        $newVal = max(0, (float) $balance->$col + $delta);
        $balance->update([$col => $newVal]);
    }

    private function countWorkingDays(Carbon $start, Carbon $end, ?int $companyId = null): float
    {
        // Carregar festius de l'empresa dins del rang (+ recurrents)
        $holidays = collect();
        if ($companyId) {
            $holidays = Holiday::where('company_id', $companyId)
                ->where(function ($q) use ($start, $end) {
                    $q->where('recurring', true)
                      ->orWhereBetween('date', [$start->toDateString(), $end->toDateString()]);
                })
                ->get();
        }

        $days    = 0;
        $current = $start->copy()->startOfDay();
        $endDay  = $end->copy()->startOfDay();
        while ($current->lte($endDay)) {
            if (! $current->isWeekend()) {
                $dateStr = $current->toDateString();   // YYYY-MM-DD
                $mmdd    = substr($dateStr, 5);        // MM-DD
                $isHoliday = $holidays->first(function ($h) use ($dateStr, $mmdd) {
                    return $h->recurring
                        ? substr($h->date->format('Y-m-d'), 5) === $mmdd
                        : $h->date->format('Y-m-d') === $dateStr;
                });
                if (! $isHoliday) $days++;
            }
            $current->addDay();
        }
        return (float) $days;
    }
}
