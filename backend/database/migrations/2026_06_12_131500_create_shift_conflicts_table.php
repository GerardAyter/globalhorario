<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_conflicts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assignment_a_id')->constrained('shift_assignments');
            $table->foreignId('assignment_b_id')->constrained('shift_assignments');
            $table->string('type')->nullable();
            $table->timestamp('detected_at')->nullable();
            $table->enum('resolution', ['pending','resolved'])->default('pending');
            $table->text('resolution_note')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_conflicts');
    }
};
