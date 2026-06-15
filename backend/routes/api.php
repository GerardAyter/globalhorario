<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\TimeEntryController;
use App\Http\Controllers\Api\ShiftController;

use App\Http\Middleware\SetTenantFromRequest;

use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\WhitelabelConfigController;

Route::prefix('v1')->group(function () {
    // Tenant management endpoints are public of the tenant-middleware so they can be created/managed from admin
    Route::apiResource('tenants', TenantController::class);
    Route::apiResource('whitelabel-configs', WhitelabelConfigController::class);

    // Other resources (tenant middleware applied globally)
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('employees', \App\Http\Controllers\Api\EmployeeController::class);
    Route::get('employees/{id}/time-entries', [\App\Http\Controllers\Api\EmployeeController::class, 'timeEntries']);
    Route::apiResource('shifts', ShiftController::class);
    Route::apiResource('time-entries', TimeEntryController::class);
    // HR resources
    Route::apiResource('absence-types', \App\Http\Controllers\Api\AbsenceTypeController::class);
    Route::apiResource('absence-requests', \App\Http\Controllers\Api\AbsenceRequestController::class);
    Route::post('absence-requests/{id}/approve', [\App\Http\Controllers\Api\AbsenceRequestController::class, 'approve']);
    Route::post('absence-requests/{id}/deny', [\App\Http\Controllers\Api\AbsenceRequestController::class, 'deny']);

    Route::apiResource('vacation-balances', \App\Http\Controllers\Api\VacationBalanceController::class);

    Route::apiResource('overtime-policies', \App\Http\Controllers\Api\OvertimePolicyController::class);
    Route::apiResource('overtimes', \App\Http\Controllers\Api\OvertimeController::class);
    Route::post('overtimes/{id}/approve', [\App\Http\Controllers\Api\OvertimeController::class, 'approve']);

    Route::apiResource('policy-absences', \App\Http\Controllers\Api\PolicyAbsenceController::class);
    Route::apiResource('policy-schedules', \App\Http\Controllers\Api\PolicyScheduleController::class);
    Route::apiResource('work-calendars', \App\Http\Controllers\Api\WorkCalendarController::class);

    Route::apiResource('contracts', \App\Http\Controllers\Api\ContractController::class);
    Route::apiResource('payrolls', \App\Http\Controllers\Api\PayrollController::class);

    Route::apiResource('shift-conflicts', \App\Http\Controllers\Api\ShiftConflictController::class);
    Route::post('shift-conflicts/{id}/resolve', [\App\Http\Controllers\Api\ShiftConflictController::class, 'resolve']);

    Route::apiResource('shift-assignments', \App\Http\Controllers\Api\ShiftAssignmentController::class);
    // Additional resources will be registered as implemented
});
