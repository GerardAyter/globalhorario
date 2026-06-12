<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\TimeEntryController;
use App\Http\Controllers\Api\ShiftController;

Route::prefix('v1')->group(function () {
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('shifts', ShiftController::class);
    Route::apiResource('time-entries', TimeEntryController::class);
    // Additional resources will be registered as implemented
});
