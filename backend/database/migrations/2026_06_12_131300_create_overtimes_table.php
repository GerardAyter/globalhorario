<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('overtime_policy_id')->nullable()->constrained('overtime_policies');
            $table->date('date');
            $table->decimal('hours', 8, 2)->default(0);
            $table->string('compensation_type')->nullable();
            $table->enum('status', ['pending','approved','compensated'])->default('pending');
            $table->decimal('compensated_hours', 8, 2)->default(0);
            $table->date('compensation_date')->nullable();
            $table->foreignId('approver_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
