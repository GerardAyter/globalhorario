<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('daily_hours', 5, 2)->default(8.00);
            $table->decimal('weekly_overtime_max', 5, 2)->nullable();
            $table->decimal('monthly_overtime_max', 5, 2)->nullable();
            $table->decimal('annual_overtime_max', 5, 2)->nullable();
            $table->unsignedSmallInteger('vacation_days')->default(23);
            $table->unsignedSmallInteger('personal_days')->default(6);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenis');
    }
};
