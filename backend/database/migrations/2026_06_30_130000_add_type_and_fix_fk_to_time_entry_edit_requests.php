<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Allow time_entry_id to be null so approved delete-requests survive entry deletion
        DB::statement('ALTER TABLE time_entry_edit_requests MODIFY COLUMN time_entry_id BIGINT UNSIGNED NULL');

        $fk = 'time_entry_edit_requests_time_entry_id_foreign';
        DB::statement("ALTER TABLE time_entry_edit_requests DROP FOREIGN KEY {$fk}");
        DB::statement("ALTER TABLE time_entry_edit_requests ADD CONSTRAINT {$fk}
            FOREIGN KEY (time_entry_id) REFERENCES time_entries(id) ON DELETE SET NULL");

        // Distinguish edit vs delete requests
        DB::statement("ALTER TABLE time_entry_edit_requests
            ADD COLUMN type ENUM('edit','delete') NOT NULL DEFAULT 'edit' AFTER id");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE time_entry_edit_requests DROP COLUMN type');

        $fk = 'time_entry_edit_requests_time_entry_id_foreign';
        DB::statement("ALTER TABLE time_entry_edit_requests DROP FOREIGN KEY {$fk}");
        DB::statement("ALTER TABLE time_entry_edit_requests ADD CONSTRAINT {$fk}
            FOREIGN KEY (time_entry_id) REFERENCES time_entries(id) ON DELETE CASCADE");
        DB::statement('ALTER TABLE time_entry_edit_requests MODIFY COLUMN time_entry_id BIGINT UNSIGNED NOT NULL');
    }
};
