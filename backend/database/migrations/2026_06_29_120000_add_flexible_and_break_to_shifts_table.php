<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->boolean('flexible_entry')->default(false)->after('active');
            $table->time('flex_entry_from')->nullable()->after('flexible_entry');
            $table->time('flex_entry_to')->nullable()->after('flex_entry_from');
            $table->integer('break_duration')->nullable()->after('flex_entry_to');
            $table->time('break_from')->nullable()->after('break_duration');
            $table->time('break_to')->nullable()->after('break_from');
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn([
                'flexible_entry', 'flex_entry_from', 'flex_entry_to',
                'break_duration', 'break_from', 'break_to',
            ]);
        });
    }
};
