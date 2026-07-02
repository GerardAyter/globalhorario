<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\WorkplaceController;
use App\Http\Controllers\Api\TimeEntryController;
use App\Http\Controllers\Api\TimeTrackingController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\WhitelabelConfigController;
use App\Http\Controllers\Api\AbsenceRequestController;
use App\Http\Controllers\Api\VacationBalanceController;
use App\Http\Controllers\Api\AbsenceTypeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\ConveniController;
use App\Http\Controllers\Api\TimeEntryEditRequestController;

// ── Públic ──────────────────────────────────────────────────────────────────
Route::post('auth/login',        [AuthController::class, 'login']);
Route::post('auth/set-password', [AuthController::class, 'setPassword']);

// ── Autenticat ──────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::get('auth/me', [AuthController::class, 'me']);
    Route::patch('auth/profile', [AuthController::class, 'updateProfile']);

    Route::prefix('v1')->group(function () {

        // ── Founder ─────────────────────────────────────────────────────────
        // Gestió global de tenants i configuració whitelabel
        Route::middleware('role:founder')->group(function () {
            Route::apiResource('tenants', TenantController::class);
            Route::apiResource('whitelabel-configs', WhitelabelConfigController::class);
        });

        // ── Superadmin+ ─────────────────────────────────────────────────────
        // Gestió d'empreses dins del tenant
        Route::middleware('role:superadmin')->group(function () {
            Route::apiResource('companies', CompanyController::class);
        });

        // ── Admin+ ──────────────────────────────────────────────────────────
        // Gestió completa d'una empresa: estructura, torns, polítiques, nòmines
        Route::middleware('role:admin')->group(function () {
            Route::post('holidays',        [HolidayController::class, 'store']);
            Route::put('holidays/{id}',    [HolidayController::class, 'update']);
            Route::delete('holidays/{id}', [HolidayController::class, 'destroy']);
            Route::get('convenis',             [ConveniController::class, 'index']);
            Route::post('convenis',            [ConveniController::class, 'store']);
            Route::put('convenis/{id}',        [ConveniController::class, 'update']);
            Route::delete('convenis/{id}',     [ConveniController::class, 'destroy']);
            Route::apiResource('departments', DepartmentController::class);
            Route::apiResource('workplaces', WorkplaceController::class);
            Route::apiResource('shifts', ShiftController::class);
            Route::apiResource('absence-types', \App\Http\Controllers\Api\AbsenceTypeController::class)->except(['index']);
            Route::apiResource('overtime-policies', \App\Http\Controllers\Api\OvertimePolicyController::class);
            Route::apiResource('policy-absences', \App\Http\Controllers\Api\PolicyAbsenceController::class);
            Route::apiResource('policy-schedules', \App\Http\Controllers\Api\PolicyScheduleController::class);
            Route::apiResource('work-calendars', \App\Http\Controllers\Api\WorkCalendarController::class);
            Route::apiResource('contracts', \App\Http\Controllers\Api\ContractController::class);
            Route::apiResource('payrolls', \App\Http\Controllers\Api\PayrollController::class);
            Route::apiResource('shift-conflicts', \App\Http\Controllers\Api\ShiftConflictController::class);
            Route::post('shift-conflicts/{id}/resolve', [\App\Http\Controllers\Api\ShiftConflictController::class, 'resolve']);
        });

        // ── HR+ ─────────────────────────────────────────────────────────────
        // Gestió d'empleats, assignacions de torn, saldos i aprovació de peticions
        Route::middleware('role:hr')->group(function () {
            Route::get('time-entry-edit-requests/count', [TimeEntryEditRequestController::class, 'pendingCount']);
            Route::get('time-entry-edit-requests', [TimeEntryEditRequestController::class, 'index']);
            Route::post('time-entry-edit-requests/{id}/approve', [TimeEntryEditRequestController::class, 'approve']);
            Route::post('time-entry-edit-requests/{id}/deny', [TimeEntryEditRequestController::class, 'deny']);
            Route::get('time-tracking/admin/entries-month', [TimeTrackingController::class, 'adminEntriesMonth']);
            Route::post('time-tracking/admin/entries/{id}/clock-out',      [TimeTrackingController::class, 'adminClockOut']);
            Route::post('time-tracking/admin/entries/{id}/edit-request',   [TimeEntryEditRequestController::class, 'storeAdminEntryEdit']);
            Route::post('time-tracking/admin/entries/{id}/delete-request', [TimeEntryEditRequestController::class, 'storeAdminEntryDelete']);
            Route::post('time-tracking/admin/breaks/{id}/edit-request',    [TimeEntryEditRequestController::class, 'storeAdminBreakEdit']);
            Route::post('time-tracking/admin/breaks/{id}/delete-request',  [TimeEntryEditRequestController::class, 'storeAdminBreakDelete']);
            Route::get('time-tracking/company-entries', [TimeTrackingController::class, 'companyEntries']);
            Route::apiResource('employees', \App\Http\Controllers\Api\EmployeeController::class);
            Route::get('employees/{id}/time-entries', [\App\Http\Controllers\Api\EmployeeController::class, 'timeEntries']);
            Route::post('employees/{id}/send-invitation', [\App\Http\Controllers\Api\EmployeeController::class, 'sendInvitation']);
            Route::apiResource('vacation-balances', \App\Http\Controllers\Api\VacationBalanceController::class);
            Route::apiResource('shift-assignments', \App\Http\Controllers\Api\ShiftAssignmentController::class);
            Route::get('absence-requests', [AbsenceRequestController::class, 'index']);
            Route::post('absence-requests/{id}/approve', [AbsenceRequestController::class, 'approve']);
            Route::post('absence-requests/{id}/deny', [AbsenceRequestController::class, 'deny']);
            Route::apiResource('vacation-balances', VacationBalanceController::class)->except(['index']);
            Route::post('overtimes/{id}/approve', [\App\Http\Controllers\Api\OvertimeController::class, 'approve']);
        });

        // ── User+ ───────────────────────────────────────────────────────────
        // Fitxatge, sol·licituds i hores extra (els controllers filtren per propietat)
        Route::get('time-entry-edit-requests/my-incoming',        [TimeEntryEditRequestController::class, 'myIncoming']);
        Route::post('time-entry-edit-requests/{id}/employee-approve', [TimeEntryEditRequestController::class, 'employeeApprove']);
        Route::post('time-entry-edit-requests/{id}/employee-deny',    [TimeEntryEditRequestController::class, 'employeeDeny']);
        Route::prefix('time-tracking')->group(function () {
            Route::get('today',            [TimeTrackingController::class, 'today']);
            Route::get('my-history',       [TimeTrackingController::class, 'myHistory']);
            Route::get('my-history-month', [TimeTrackingController::class, 'myHistoryMonth']);
            Route::post('clock-in',    [TimeTrackingController::class, 'clockIn']);
            Route::post('clock-out',   [TimeTrackingController::class, 'clockOut']);
            Route::post('break-start', [TimeTrackingController::class, 'breakStart']);
            Route::post('break-end',   [TimeTrackingController::class, 'breakEnd']);
            Route::post('entries/{id}/edit-request',         [TimeEntryEditRequestController::class, 'store']);
            Route::post('entries/{id}/delete-request',       [TimeEntryEditRequestController::class, 'storeDelete']);
            Route::post('breaks/{breakId}/edit-request',     [TimeEntryEditRequestController::class, 'storeBreakEdit']);
            Route::post('breaks/{breakId}/delete-request',   [TimeEntryEditRequestController::class, 'storeBreakDelete']);
        });
        Route::apiResource('time-entries', TimeEntryController::class);

        // Festius (lectura per a tots els usuaris)
        Route::get('holidays', [HolidayController::class, 'index']);

        // Notificacions
        Route::get('notifications/my',          [NotificationController::class, 'my']);
        Route::post('notifications/read-all',   [NotificationController::class, 'readAll']);
        Route::post('notifications/{id}/read',  [NotificationController::class, 'read']);

        // Absències: lectura de tipus i gestió de les pròpies sol·licituds
        Route::get('absence-types', [AbsenceTypeController::class, 'index']);
        Route::get('absence-requests/my', [AbsenceRequestController::class, 'my']);
        Route::get('vacation-balances/my', [VacationBalanceController::class, 'my']);
        Route::apiResource('absence-requests', AbsenceRequestController::class)->except(['index', 'update']);
        Route::delete('absence-requests/{id}/cancel', [AbsenceRequestController::class, 'destroy']);

        Route::apiResource('overtimes', \App\Http\Controllers\Api\OvertimeController::class);
    });
});
