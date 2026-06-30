<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('convenis', function (Blueprint $table) {
            $table->renameColumn('daily_hours', 'weekly_hours');
        });
    }

    public function down(): void
    {
        Schema::table('convenis', function (Blueprint $table) {
            $table->renameColumn('weekly_hours', 'daily_hours');
        });
    }
};
