<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // TIMESTAMP columns in MySQL auto-update on row changes; DATETIME does not.
        // Store all user-facing times as DATETIME so MySQL never silently mutates them.
        DB::statement('ALTER TABLE time_entries MODIFY COLUMN clock_in_at DATETIME NULL');
        DB::statement('ALTER TABLE time_entries MODIFY COLUMN clock_out_at DATETIME NULL');

        DB::statement('ALTER TABLE time_entry_breaks MODIFY COLUMN break_start_at DATETIME NOT NULL');
        DB::statement('ALTER TABLE time_entry_breaks MODIFY COLUMN break_end_at DATETIME NULL');

        DB::statement('ALTER TABLE time_entry_logs MODIFY COLUMN happened_at DATETIME NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE time_entries MODIFY COLUMN clock_in_at TIMESTAMP NULL');
        DB::statement('ALTER TABLE time_entries MODIFY COLUMN clock_out_at TIMESTAMP NULL');

        DB::statement('ALTER TABLE time_entry_breaks MODIFY COLUMN break_start_at TIMESTAMP NOT NULL');
        DB::statement('ALTER TABLE time_entry_breaks MODIFY COLUMN break_end_at TIMESTAMP NULL');

        DB::statement('ALTER TABLE time_entry_logs MODIFY COLUMN happened_at TIMESTAMP NOT NULL');
    }
};
