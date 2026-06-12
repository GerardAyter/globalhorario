<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('vacation_days_per_year', 8, 2)->nullable();
            $table->integer('personal_days')->nullable();
            $table->integer('max_consecutive_days')->nullable();
            $table->integer('min_notice_days')->nullable();
            $table->boolean('allow_accumulation')->default(false);
            $table->integer('max_accumulated_days')->nullable();
            $table->boolean('approval_required')->default(true);
            $table->integer('approval_levels')->default(1);
            $table->string('applies_to')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_absences');
    }
};
