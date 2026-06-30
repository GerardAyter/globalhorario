<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE time_entry_edit_requests
            ADD COLUMN initiated_by ENUM('employee','admin') NOT NULL DEFAULT 'employee' AFTER type");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE time_entry_edit_requests DROP COLUMN initiated_by');
    }
};
