<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['indefinite','temporary','project'])->default('indefinite');
            $table->enum('work_time', ['full','part'])->default('full');
            $table->decimal('weekly_hours', 8, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('annual_gross_salary', 12, 2)->nullable();
            $table->decimal('tax_percentage', 5, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->string('termination_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
