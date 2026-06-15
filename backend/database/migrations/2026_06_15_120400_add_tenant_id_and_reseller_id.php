<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure there's at least one tenant to default to
        if (DB::table('tenants')->count() === 0) {
            DB::table('tenants')->insert([
                'nom_intern' => 'default',
                'pla' => 'starter',
                'max_empleats' => 0,
                'actiu' => true,
                'data_alta' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $defaultTenantId = DB::table('tenants')->orderBy('id')->first()->id;

        $tables = [
            'companies','departments','workplaces','users','time_entries','breaks','shifts','shift_assignments','shift_conflicts','absence_types','absence_requests','vacation_balances','contracts','work_calendars','policy_absences','policy_schedules','overtime_policies','overtimes','payrolls','notifications_custom','audit_logs'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table, $defaultTenantId) {
                    if (! Schema::hasColumn($table, 'tenant_id')) {
                        $t->foreignId('tenant_id')->default($defaultTenantId)->constrained('tenants')->cascadeOnDelete();
                    }
                });
            }
        }

        // Add reseller_id to companies
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                if (! Schema::hasColumn('companies', 'reseller_id')) {
                    $table->foreignId('reseller_id')->nullable()->constrained('resellers');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'companies','departments','workplaces','users','time_entries','breaks','shifts','shift_assignments','shift_conflicts','absence_types','absence_requests','vacation_balances','contracts','work_calendars','policy_absences','policy_schedules','overtime_policies','overtimes','payrolls','notifications_custom','audit_logs'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    if (Schema::hasColumn($table, 'tenant_id')) {
                        $t->dropForeign([$table.'_tenant_id_foreign']);
                        $t->dropColumn('tenant_id');
                    }
                });
            }
        }

        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                if (Schema::hasColumn('companies', 'reseller_id')) {
                    $table->dropForeign(['reseller_id']);
                    $table->dropColumn('reseller_id');
                }
            });
        }
    }
};
