<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('color')->nullable();
            $table->enum('type', ['fixed','rotating','flexible'])->default('fixed');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('crosses_midnight')->default(false);
            $table->json('days_of_week')->nullable();
            $table->decimal('total_hours', 8, 2)->nullable();
            $table->integer('min_rest_after')->nullable();
            $table->string('location_required')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
