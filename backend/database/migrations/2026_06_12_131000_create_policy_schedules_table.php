<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['fixed','flexible','shift'])->default('fixed');
            $table->integer('tolerance_minutes')->default(0);
            $table->boolean('require_geolocation')->default(false);
            $table->integer('geolocation_radius_meters')->nullable();
            $table->boolean('allow_remote_clocking')->default(true);
            $table->decimal('max_hours_per_day', 8, 2)->nullable();
            $table->integer('min_rest_between_shifts')->nullable();
            $table->boolean('require_approval_for_records')->default(false);
            $table->string('auto_approve_if')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_schedules');
    }
};
