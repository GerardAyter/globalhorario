<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('convenis', function (Blueprint $table) {
            $table->unsignedSmallInteger('break_minutes')->default(0)->after('daily_hours');
        });
    }

    public function down(): void
    {
        Schema::table('convenis', function (Blueprint $table) {
            $table->dropColumn('break_minutes');
        });
    }
};
