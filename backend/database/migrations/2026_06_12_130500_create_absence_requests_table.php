<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absence_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('absence_type_id')->constrained('absence_types');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('working_days', 8, 2)->nullable();
            $table->boolean('half_day_start')->default(false);
            $table->boolean('half_day_end')->default(false);
            $table->enum('status', ['pending','approved','denied'])->default('pending');
            $table->text('employee_comment')->nullable();
            $table->text('manager_comment')->nullable();
            $table->string('attachment_url')->nullable();
            $table->json('approvers')->nullable();
            $table->integer('current_approval_index')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_requests');
    }
};
