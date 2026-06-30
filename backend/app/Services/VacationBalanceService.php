<?php

namespace App\Services;

use App\Models\AbsenceRequest;
use App\Models\User;
use App\Models\VacationBalance;

class VacationBalanceService extends BaseService
{
    /** Balanç de l'any en curs per a l'usuari autenticat */
    public function myBalance(User $user): array
    {
        $employee = $user->employee;
        $year     = now()->year;

        $balance = VacationBalance::where('user_id', $user->id)
            ->where('year', $year)
            ->first();

        // Dies de vacances
        $generated  = (float) ($balance?->generated_days       ?? 0);
        $carried    = (float) ($balance?->carried_from_previous ?? 0);
        $extra      = (float) ($balance?->manual_adjustment     ?? 0);
        $taken      = (float) ($balance?->taken_days            ?? 0);
        $pending    = (float) ($balance?->pending_days          ?? 0);
        $total      = $generated + $carried + $extra;
        $available  = max(0, $total - $taken - $pending);

        // Dies personals (comptats a partir de les requests aprovades/pendents)
        $personalTotal   = (int) ($balance?->personal_days_total ?? 0);
        $personalTaken   = $this->countPersonalDays($user->id, AbsenceRequest::STATUS_APPROVED, $year);
        $personalPending = $this->countPersonalDays($user->id, AbsenceRequest::STATUS_PENDING, $year);
        $personalAvail   = max(0, $personalTotal - $personalTaken - $personalPending);

        return [
            'vacation' => [
                'generated'   => $generated,
                'carried'     => $carried,
                'extra'       => $extra,
                'total'       => $total,
                'taken'       => $taken,
                'pending'     => $pending,
                'available'   => $available,
            ],
            'personal' => [
                'total'     => $personalTotal,
                'taken'     => $personalTaken,
                'pending'   => $personalPending,
                'available' => $personalAvail,
            ],
        ];
    }

    /** Balanços de tots els empleats de l'empresa (HR+) */
    public function companyBalances(User $user): \Illuminate\Support\Collection
    {
        $companyId = $user->company_id ?? $user->employee?->company_id;

        return VacationBalance::with('employee.department')
            ->where('company_id', $companyId)
            ->where('year', now()->year)
            ->get();
    }

    // ── Privat ──────────────────────────────────────────────────────────────────

    private function countPersonalDays(int $userId, string $status, int $year): float
    {
        return (float) AbsenceRequest::where('user_id', $userId)
            ->where('status', $status)
            ->whereYear('start_date', $year)
            ->whereHas('type', fn ($q) => $q->where('category', 'personal'))
            ->sum('working_days');
    }
}
