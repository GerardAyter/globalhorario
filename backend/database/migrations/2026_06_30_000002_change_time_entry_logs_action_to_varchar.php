<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE time_entry_logs MODIFY COLUMN action VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE time_entry_logs MODIFY COLUMN action ENUM('clock_in','clock_out','break_start','break_end') NOT NULL");
    }
};
