<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entry_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('time_entry_id')->constrained('time_entries')->cascadeOnDelete();
            $table->enum('action', ['clock_in', 'clock_out', 'break_start', 'break_end']);
            $table->timestamp('happened_at');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // No updated_at: log immutable
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entry_logs');
    }
};
