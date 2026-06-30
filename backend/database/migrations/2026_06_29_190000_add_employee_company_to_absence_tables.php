<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absence_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('absence_requests', 'employee_id')) {
                $table->foreignId('employee_id')->nullable()->after('user_id')
                      ->constrained('employees')->nullOnDelete();
            }
            if (! Schema::hasColumn('absence_requests', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('employee_id')
                      ->constrained('companies')->nullOnDelete();
            }
        });

        Schema::table('vacation_balances', function (Blueprint $table) {
            if (! Schema::hasColumn('vacation_balances', 'employee_id')) {
                $table->foreignId('employee_id')->nullable()->after('user_id')
                      ->constrained('employees')->nullOnDelete();
            }
            if (! Schema::hasColumn('vacation_balances', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('employee_id')
                      ->constrained('companies')->nullOnDelete();
            }
            if (! Schema::hasColumn('vacation_balances', 'personal_days_total')) {
                $table->smallInteger('personal_days_total')->default(0)->after('manual_adjustment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('absence_requests', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Employee::class);
            $table->dropColumn(['employee_id', 'company_id']);
        });
        Schema::table('vacation_balances', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Employee::class);
            $table->dropColumn(['employee_id', 'company_id', 'personal_days_total']);
        });
    }
};
