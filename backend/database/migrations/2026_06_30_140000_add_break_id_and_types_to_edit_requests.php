<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE time_entry_edit_requests
            MODIFY COLUMN type ENUM('edit','delete','break_edit','break_delete') NOT NULL DEFAULT 'edit'");

        DB::statement('ALTER TABLE time_entry_edit_requests
            ADD COLUMN break_id BIGINT UNSIGNED NULL AFTER time_entry_id');

        DB::statement('ALTER TABLE time_entry_edit_requests
            ADD CONSTRAINT time_entry_edit_requests_break_id_foreign
            FOREIGN KEY (break_id) REFERENCES time_entry_breaks(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        $fk = 'time_entry_edit_requests_break_id_foreign';
        DB::statement("ALTER TABLE time_entry_edit_requests DROP FOREIGN KEY {$fk}");
        DB::statement('ALTER TABLE time_entry_edit_requests DROP COLUMN break_id');

        DB::statement("ALTER TABLE time_entry_edit_requests
            MODIFY COLUMN type ENUM('edit','delete') NOT NULL DEFAULT 'edit'");
    }
};
