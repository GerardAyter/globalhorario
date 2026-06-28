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
            DB::statement("ALTER TABLE `overtimes` MODIFY COLUMN `status` ENUM('pending','approved','denied','cancelled','compensated') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('overtimes', function (Blueprint $table) {
                $table->enum('status', ['pending', 'approved', 'denied', 'cancelled', 'compensated'])->default('pending')->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `overtimes` MODIFY COLUMN `status` ENUM('pending','approved','compensated') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('overtimes', function (Blueprint $table) {
                $table->enum('status', ['pending', 'approved', 'compensated'])->default('pending')->change();
            });
        }
    }
};
