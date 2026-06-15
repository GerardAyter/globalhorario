<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'time_entries',
            'breaks',
            'absence_requests',
            'vacation_balances',
            'contracts',
            'overtimes',
            'payrolls',
            'shift_assignments',
            'shift_conflicts',
            'notifications_custom',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                // keep user_id for compatibility, ensure nullable
                if (! Schema::hasColumn($table, 'user_id')) {
                    $t->foreignId('user_id')->nullable()->constrained('users')->after('tenant_id');
                } else {
                    $t->foreignId('user_id')->nullable()->change();
                }

                // add employee_id nullable
                if (! Schema::hasColumn($table, 'employee_id')) {
                    $t->unsignedBigInteger('employee_id')->nullable()->after('user_id');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'time_entries',
            'breaks',
            'absence_requests',
            'vacation_balances',
            'contracts',
            'overtimes',
            'payrolls',
            'shift_assignments',
            'shift_conflicts',
            'notifications_custom',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'employee_id')) {
                    $t->dropForeign([$table.'_employee_id_foreign']);
                    $t->dropColumn('employee_id');
                }
                if (Schema::hasColumn($table, 'user_id')) {
                    // do not drop user_id for safety
                }
            });
        }
    }
};
