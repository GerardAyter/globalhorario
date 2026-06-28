<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\WorkplaceController;
use App\Http\Controllers\Api\TimeEntryController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\WhitelabelConfigController;

// ── Públic ──────────────────────────────────────────────────────────────────
Route::post('auth/login', [AuthController::class, 'login']);

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
            Route::apiResource('departments', DepartmentController::class);
            Route::apiResource('workplaces', WorkplaceController::class);
            Route::apiResource('shifts', ShiftController::class);
            Route::apiResource('absence-types', \App\Http\Controllers\Api\AbsenceTypeController::class);
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
            Route::apiResource('employees', \App\Http\Controllers\Api\EmployeeController::class);
            Route::get('employees/{id}/time-entries', [\App\Http\Controllers\Api\EmployeeController::class, 'timeEntries']);
            Route::apiResource('vacation-balances', \App\Http\Controllers\Api\VacationBalanceController::class);
            Route::apiResource('shift-assignments', \App\Http\Controllers\Api\ShiftAssignmentController::class);
            Route::post('absence-requests/{id}/approve', [\App\Http\Controllers\Api\AbsenceRequestController::class, 'approve']);
            Route::post('absence-requests/{id}/deny', [\App\Http\Controllers\Api\AbsenceRequestController::class, 'deny']);
            Route::post('overtimes/{id}/approve', [\App\Http\Controllers\Api\OvertimeController::class, 'approve']);
        });

        // ── User+ ───────────────────────────────────────────────────────────
        // Fitxatge, sol·licituds i hores extra (els controllers filtren per propietat)
        Route::apiResource('time-entries', TimeEntryController::class);
        Route::apiResource('absence-requests', \App\Http\Controllers\Api\AbsenceRequestController::class);
        Route::apiResource('overtimes', \App\Http\Controllers\Api\OvertimeController::class);
    });
});
