<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `absence_requests` MODIFY COLUMN `status` ENUM('pending','approved','denied','cancelled') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('absence_requests', function (Blueprint $table) {
                $table->enum('status', ['pending', 'approved', 'denied', 'cancelled'])->default('pending')->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `absence_requests` MODIFY COLUMN `status` ENUM('pending','approved','denied') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('absence_requests', function (Blueprint $table) {
                $table->enum('status', ['pending', 'approved', 'denied'])->default('pending')->change();
            });
        }
    }
};
