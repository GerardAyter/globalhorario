<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('time_entries', 'employee_id')) {
                $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete()->after('user_id');
            }
            if (! Schema::hasColumn('time_entries', 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete()->after('employee_id');
            }
            if (! Schema::hasColumn('time_entries', 'shift_id')) {
                $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete()->after('company_id');
            }
            if (! Schema::hasColumn('time_entries', 'date')) {
                $table->date('date')->nullable()->after('shift_id');
            }
            if (! Schema::hasColumn('time_entries', 'work_status')) {
                $table->enum('work_status', ['clocked_in', 'on_break', 'clocked_out'])->nullable()->after('date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shift_id');
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('employee_id');
            $table->dropColumn(['date', 'work_status']);
        });
    }
};
