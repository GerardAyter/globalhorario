<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('ordinary_hours', 8, 2)->default(0);
            $table->decimal('overtime_rem_pay', 8, 2)->default(0);
            $table->decimal('gross_salary', 12, 2)->default(0);
            $table->decimal('tax_withholding', 12, 2)->default(0);
            $table->decimal('employee_social_security', 12, 2)->default(0);
            $table->decimal('employer_social_security', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->decimal('paid_days_paid_leave', 8, 2)->default(0);
            $table->decimal('sick_days', 8, 2)->default(0);
            $table->enum('status', ['draft','closed','paid'])->default('draft');
            $table->string('pdf_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
