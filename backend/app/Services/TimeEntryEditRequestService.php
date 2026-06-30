<?php

namespace App\Services;

use App\Models\TimeEntry;
use App\Models\TimeEntryBreak;
use App\Models\TimeEntryEditRequest;
use App\Models\TimeEntryLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TimeEntryEditRequestService
{
    public function __construct(private NotificationService $notifications) {}

    // ── Empleat sol·licita ────────────────────────────────────────────────────

    public function createRequest(User $user, TimeEntry $entry, array $requestedData, string $reason): TimeEntryEditRequest
    {
        $this->ensureNoPendingEntryRequest($entry);
        return $this->buildRequest('edit', 'employee', $user, $entry, null,
            $this->normalizeTimestamps($requestedData, ['clock_in_at', 'clock_out_at']),
            $this->entrySnapshot($entry), $reason);
    }

    public function createDeleteRequest(User $user, TimeEntry $entry, string $reason): TimeEntryEditRequest
    {
        $this->ensureNoPendingEntryRequest($entry);
        return $this->buildRequest('delete', 'employee', $user, $entry, null, [], $this->entrySnapshot($entry), $reason);
    }

    public function createBreakEditRequest(User $user, TimeEntryBreak $break, array $requestedData, string $reason): TimeEntryEditRequest
    {
        $this->ensureNoPendingBreakRequest($break);
        $entry = $break->timeEntry;
        return $this->buildRequest('break_edit', 'employee', $user, $entry, $break,
            $this->normalizeTimestamps($requestedData, ['break_start_at', 'break_end_at']),
            $this->breakSnapshot($break), $reason);
    }

    public function createBreakDeleteRequest(User $user, TimeEntryBreak $break, string $reason): TimeEntryEditRequest
    {
        $this->ensureNoPendingBreakRequest($break);
        $entry = $break->timeEntry;
        return $this->buildRequest('break_delete', 'employee', $user, $entry, $break, [], $this->breakSnapshot($break), $reason);
    }

    // ── Admin sol·licita ──────────────────────────────────────────────────────

    public function createAdminEntryEdit(User $admin, TimeEntry $entry, array $requestedData, string $reason): TimeEntryEditRequest
    {
        $this->ensureNoPendingEntryRequest($entry);
        return $this->buildRequest('edit', 'admin', $admin, $entry, null,
            $this->normalizeTimestamps($requestedData, ['clock_in_at', 'clock_out_at']),
            $this->entrySnapshot($entry), $reason);
    }

    public function createAdminEntryDelete(User $admin, TimeEntry $entry, string $reason): TimeEntryEditRequest
    {
        $this->ensureNoPendingEntryRequest($entry);
        return $this->buildRequest('delete', 'admin', $admin, $entry, null, [], $this->entrySnapshot($entry), $reason);
    }

    public function createAdminBreakEdit(User $admin, TimeEntryBreak $break, array $requestedData, string $reason): TimeEntryEditRequest
    {
        $this->ensureNoPendingBreakRequest($break);
        $entry = $break->timeEntry;
        return $this->buildRequest('break_edit', 'admin', $admin, $entry, $break,
            $this->normalizeTimestamps($requestedData, ['break_start_at', 'break_end_at']),
            $this->breakSnapshot($break), $reason);
    }

    public function createAdminBreakDelete(User $admin, TimeEntryBreak $break, string $reason): TimeEntryEditRequest
    {
        $this->ensureNoPendingBreakRequest($break);
        $entry = $break->timeEntry;
        return $this->buildRequest('break_delete', 'admin', $admin, $entry, $break, [], $this->breakSnapshot($break), $reason);
    }

    // ── Admin revisa (empleat havia sol·licitat) ──────────────────────────────

    public function approve(User $admin, TimeEntryEditRequest $req, ?string $note): void
    {
        $this->doReview($req, 'approved', $admin, $note);
        $this->applyChange($req, $admin);

        $date = $req->original_data['date'] ?? $req->timeEntry?->date?->format('Y-m-d') ?? '—';
        $this->notifications->send(
            $req->requested_by_user_id,
            $req->type . '_approved',
            'Sol·licitud aprovada',
            $this->approvalMessage($req->type, $date, true),
            ['url' => '/time-entries']
        );
    }

    public function deny(User $admin, TimeEntryEditRequest $req, ?string $note): void
    {
        $this->doReview($req, 'denied', $admin, $note);

        $date = $req->original_data['date'] ?? $req->timeEntry?->date?->format('Y-m-d') ?? '—';
        $body = $this->approvalMessage($req->type, $date, false);
        if ($note) $body .= ' Motiu: ' . $note;

        $this->notifications->send(
            $req->requested_by_user_id,
            $req->type . '_denied',
            'Sol·licitud denegada',
            $body,
            ['url' => '/time-entries']
        );
    }

    // ── Empleat revisa (admin havia sol·licitat) ──────────────────────────────

    public function approveAsEmployee(User $employee, TimeEntryEditRequest $req, ?string $note): void
    {
        if ($req->initiated_by !== 'admin') throw new \Exception('Aquesta sol·licitud no és d\'un administrador.');
        $this->ensureEmployeeOwnsRequest($employee, $req);

        $this->doReview($req, 'approved', $employee, $note);
        $this->applyChange($req, $employee);

        $date = $req->original_data['date'] ?? $req->timeEntry?->date?->format('Y-m-d') ?? '—';
        $this->notifications->send(
            $req->requested_by_user_id,
            'admin_request_approved',
            'Canvi aprovat per l\'empleat',
            $employee->name . ' ha aprovat el canvi sol·licitat del ' . $this->formatDate($date) . '.',
            ['url' => '/time-entry-edit-requests']
        );
    }

    public function denyAsEmployee(User $employee, TimeEntryEditRequest $req, ?string $note): void
    {
        if ($req->initiated_by !== 'admin') throw new \Exception('Aquesta sol·licitud no és d\'un administrador.');
        $this->ensureEmployeeOwnsRequest($employee, $req);

        $this->doReview($req, 'denied', $employee, $note);

        $date = $req->original_data['date'] ?? $req->timeEntry?->date?->format('Y-m-d') ?? '—';
        $body = $employee->name . ' ha denegat el canvi sol·licitat del ' . $this->formatDate($date) . '.';
        if ($note) $body .= ' Motiu: ' . $note;

        $this->notifications->send(
            $req->requested_by_user_id,
            'admin_request_denied',
            'Canvi denegat per l\'empleat',
            $body,
            ['url' => '/time-entry-edit-requests']
        );
    }

    // ── Llistats ──────────────────────────────────────────────────────────────

    public function pendingCountForCompany(User $admin): int
    {
        $companyId = $admin->employee?->company_id ?? $admin->company_id;
        if (! $companyId) return 0;

        return TimeEntryEditRequest::whereHas('employee', fn($q) => $q->where('company_id', $companyId))
            ->where('status', 'pending')
            ->where('initiated_by', 'employee')
            ->count();
    }

    public function pendingForCompany(User $admin): Collection
    {
        $companyId = $admin->employee?->company_id ?? $admin->company_id;
        if (! $companyId) return collect();

        return TimeEntryEditRequest::with(['timeEntry', 'timeEntryBreak', 'employee', 'requestedBy'])
            ->whereHas('employee', fn($q) => $q->where('company_id', $companyId))
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();
    }

    public function allForCompany(User $admin): Collection
    {
        $companyId = $admin->employee?->company_id ?? $admin->company_id;
        if (! $companyId) return collect();

        return TimeEntryEditRequest::with(['timeEntry', 'timeEntryBreak', 'employee', 'requestedBy', 'reviewedBy'])
            ->whereHas('employee', fn($q) => $q->where('company_id', $companyId))
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();
    }

    public function pendingIncomingForEmployee(User $user): Collection
    {
        $employee = $user->employee;
        if (! $employee) return collect();

        return TimeEntryEditRequest::with(['timeEntry', 'timeEntryBreak', 'requestedBy'])
            ->where('employee_id', $employee->id)
            ->where('initiated_by', 'admin')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();
    }

    // ── Privat ────────────────────────────────────────────────────────────────

    private function buildRequest(
        string $type,
        string $initiatedBy,
        User $requester,
        TimeEntry $entry,
        ?TimeEntryBreak $break,
        array $requestedData,
        array $originalData,
        string $reason
    ): TimeEntryEditRequest {
        $req = TimeEntryEditRequest::create([
            'type'                 => $type,
            'initiated_by'         => $initiatedBy,
            'time_entry_id'        => $entry->id,
            'break_id'             => $break?->id,
            'employee_id'          => $entry->employee_id,
            'requested_by_user_id' => $requester->id,
            'reason'               => $reason,
            'original_data'        => $originalData,
            'requested_data'       => $requestedData,
            'status'               => 'pending',
        ]);

        $logAction = ($initiatedBy === 'admin' ? 'admin_' : '') . $type . '_requested';
        $meta = ['request_id' => $req->id, 'reason' => $reason];
        if ($break) $meta['break_id'] = $break->id;
        $this->logEntry($entry, $logAction, $requester, $meta);

        if ($initiatedBy === 'employee') {
            $this->notifyAdmins($entry, $req, $requester);
        } else {
            $this->notifyEmployee($entry, $req, $requester);
        }

        return $req;
    }

    private function applyChange(TimeEntryEditRequest $req, User $actor): void
    {
        $entry = $req->timeEntry;
        match ($req->type) {
            'edit'         => $this->applyEntryEdit($actor, $req, $entry),
            'delete'       => $this->applyEntryDelete($actor, $req, $entry),
            'break_edit'   => $this->applyBreakEdit($actor, $req, $entry),
            'break_delete' => $this->applyBreakDelete($actor, $req, $entry),
        };
    }

    private function doReview(TimeEntryEditRequest $req, string $status, User $reviewer, ?string $note): void
    {
        if ($req->status !== 'pending') throw new \Exception('Aquesta sol·licitud ja ha estat revisada.');
        $req->update([
            'status'              => $status,
            'reviewed_by_user_id' => $reviewer->id,
            'reviewed_at'         => now(),
            'review_note'         => $note,
        ]);
    }

    private function applyEntryEdit(User $actor, TimeEntryEditRequest $req, ?TimeEntry $entry): void
    {
        if (! $entry) return;
        $update = [];
        foreach (['clock_in_at', 'clock_out_at'] as $field) {
            if (isset($req->requested_data[$field])) $update[$field] = $req->requested_data[$field];
        }
        if ($update) $entry->update($update);
        $this->logEntry($entry, 'edit_approved', $actor, ['request_id' => $req->id]);
    }

    private function applyEntryDelete(User $actor, TimeEntryEditRequest $req, ?TimeEntry $entry): void
    {
        if (! $entry) return;
        $this->logEntry($entry, 'delete_approved', $actor, ['request_id' => $req->id]);
        $entry->delete();
    }

    private function applyBreakEdit(User $actor, TimeEntryEditRequest $req, ?TimeEntry $entry): void
    {
        $break = $req->timeEntryBreak;
        if (! $break) return;
        $update = [];
        foreach (['break_start_at', 'break_end_at'] as $field) {
            if (isset($req->requested_data[$field])) $update[$field] = $req->requested_data[$field];
        }
        if ($update) {
            $newStart = isset($update['break_start_at']) ? Carbon::parse($update['break_start_at']) : $break->break_start_at;
            $newEnd   = isset($update['break_end_at'])   ? Carbon::parse($update['break_end_at'])   : $break->break_end_at;
            if ($newStart && $newEnd) $update['duration_minutes'] = (int) $newStart->diffInMinutes($newEnd);
            $break->update($update);
        }
        if ($entry) $this->logEntry($entry, 'break_edit_approved', $actor, ['request_id' => $req->id, 'break_id' => $break->id]);
    }

    private function applyBreakDelete(User $actor, TimeEntryEditRequest $req, ?TimeEntry $entry): void
    {
        $break = $req->timeEntryBreak;
        if (! $break) return;
        if ($entry) $this->logEntry($entry, 'break_delete_approved', $actor, ['request_id' => $req->id, 'break_id' => $break->id]);
        $break->delete();
    }

    private function ensureNoPendingEntryRequest(TimeEntry $entry): void
    {
        if (TimeEntryEditRequest::where('time_entry_id', $entry->id)->whereNull('break_id')->where('status', 'pending')->exists()) {
            throw new \Exception('Ja hi ha una sol·licitud pendent per aquest fitxatge.');
        }
    }

    private function ensureNoPendingBreakRequest(TimeEntryBreak $break): void
    {
        if (TimeEntryEditRequest::where('break_id', $break->id)->where('status', 'pending')->exists()) {
            throw new \Exception('Ja hi ha una sol·licitud pendent per aquesta pausa.');
        }
    }

    private function ensureEmployeeOwnsRequest(User $user, TimeEntryEditRequest $req): void
    {
        $employeeId = $user->employee?->id;
        if (! $employeeId || $req->employee_id !== $employeeId) {
            throw new \Exception('Accés denegat.');
        }
    }

    private function notifyAdmins(TimeEntry $entry, TimeEntryEditRequest $req, User $requester): void
    {
        $employee = $entry->employee;
        if (! $employee) return;

        $admins = User::where('company_id', $employee->company_id)
            ->whereIn('role', ['admin', 'hr'])
            ->where('id', '!=', $requester->id)
            ->get();

        [$title, $body] = $this->requestNotificationText($req->type, $requester->name, $entry->date->format('d/m/Y'));

        foreach ($admins as $admin) {
            $this->notifications->send($admin->id, $req->type . '_pending', $title, $body,
                ['url' => '/time-entry-edit-requests', 'request_id' => $req->id]);
        }
    }

    private function notifyEmployee(TimeEntry $entry, TimeEntryEditRequest $req, User $admin): void
    {
        $employee = $entry->employee;
        if (! $employee?->user_id) return;

        [$title, $body] = $this->adminRequestNotificationText($req->type, $admin->name, $entry->date->format('d/m/Y'));

        $this->notifications->send($employee->user_id, 'admin_' . $req->type . '_pending', $title, $body,
            ['url' => '/time-entries', 'request_id' => $req->id]);
    }

    private function requestNotificationText(string $type, string $name, string $date): array
    {
        return match ($type) {
            'edit'         => ['Sol·licitud d\'edició de fitxatge',     "$name ha sol·licitat modificar el fitxatge del $date."],
            'delete'       => ['Sol·licitud d\'eliminació de fitxatge', "$name ha sol·licitat eliminar el fitxatge del $date."],
            'break_edit'   => ['Sol·licitud d\'edició de pausa',        "$name ha sol·licitat modificar una pausa del $date."],
            'break_delete' => ['Sol·licitud d\'eliminació de pausa',    "$name ha sol·licitat eliminar una pausa del $date."],
        };
    }

    private function adminRequestNotificationText(string $type, string $adminName, string $date): array
    {
        return match ($type) {
            'edit'         => ['L\'administrador vol modificar el teu fitxatge',      "$adminName vol modificar el teu fitxatge del $date. Aprova o denega el canvi."],
            'delete'       => ['L\'administrador vol eliminar el teu fitxatge',       "$adminName vol eliminar el teu fitxatge del $date. Aprova o denega el canvi."],
            'break_edit'   => ['L\'administrador vol modificar una pausa teva',       "$adminName vol modificar una pausa del $date. Aprova o denega el canvi."],
            'break_delete' => ['L\'administrador vol eliminar una pausa teva',        "$adminName vol eliminar una pausa del $date. Aprova o denega el canvi."],
        };
    }

    private function logEntry(TimeEntry $entry, string $action, User $user, array $metadata = []): void
    {
        TimeEntryLog::create([
            'time_entry_id' => $entry->id,
            'action'        => $action,
            'happened_at'   => now(),
            'user_id'       => $user->id,
            'employee_id'   => $entry->employee_id,
            'metadata'      => $metadata ?: null,
            'created_at'    => now(),
        ]);
    }

    private function entrySnapshot(TimeEntry $entry): array
    {
        return [
            'clock_in_at'  => $entry->clock_in_at?->toIso8601String(),
            'clock_out_at' => $entry->clock_out_at?->toIso8601String(),
            'date'         => $entry->date?->toDateString(),
        ];
    }

    private function breakSnapshot(TimeEntryBreak $break): array
    {
        return [
            'break_start_at'   => $break->break_start_at?->toIso8601String(),
            'break_end_at'     => $break->break_end_at?->toIso8601String(),
            'duration_minutes' => $break->duration_minutes,
            'date'             => $break->timeEntry?->date?->toDateString(),
        ];
    }

    private function normalizeTimestamps(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (! empty($data[$field])) {
                try { $data[$field] = Carbon::parse($data[$field])->utc()->toIso8601String(); }
                catch (\Throwable) {}
            }
        }
        return $data;
    }

    private function approvalMessage(string $type, string $date, bool $approved): string
    {
        $verb   = $approved ? 'aprovada' : 'denegada';
        $action = match ($type) {
            'edit'         => 'modificació del fitxatge',
            'delete'       => 'eliminació del fitxatge',
            'break_edit'   => 'modificació de la pausa',
            'break_delete' => 'eliminació de la pausa',
        };
        return "La teva sol·licitud d'{$action} del " . $this->formatDate($date) . " ha estat {$verb}.";
    }

    private function formatDate(string $date): string
    {
        try { return Carbon::parse($date)->format('d/m/Y'); }
        catch (\Throwable) { return $date; }
    }
}
