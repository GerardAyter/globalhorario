<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `plan_feature_flags` MODIFY `feature` ENUM(
            'whatsapp_bot','geoloc','auto_shifts','multi_approval','payroll_export','api_access',
            'time_tracking','documents','calendar'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `plan_feature_flags` MODIFY `feature` ENUM(
            'whatsapp_bot','geoloc','auto_shifts','multi_approval','payroll_export','api_access'
        ) NOT NULL");
    }
};
