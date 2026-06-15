<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_feature_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->enum('feature', ['whatsapp_bot','geoloc','auto_shifts','multi_approval','payroll_export','api_access']);
            $table->boolean('actiu')->default(false);
            $table->json('config_extra')->nullable();
            $table->date('data_expiracio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_feature_flags');
    }
};
