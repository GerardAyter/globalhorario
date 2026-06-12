<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absence_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->enum('category', ['vacation','sick','personal','other'])->default('other');
            $table->boolean('requires_document')->default(false);
            $table->boolean('paid')->default(false);
            $table->integer('max_days_per_year')->nullable();
            $table->boolean('counts_for_seniority')->default(false);
            $table->string('legal_basis')->nullable();
            $table->string('calendar_color')->nullable();
            $table->boolean('visible_to_company')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_types');
    }
};
