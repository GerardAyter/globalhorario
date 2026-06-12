<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacation_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('year');
            $table->decimal('generated_days', 8, 2)->default(0);
            $table->decimal('taken_days', 8, 2)->default(0);
            $table->decimal('pending_days', 8, 2)->default(0);
            $table->decimal('carried_from_previous', 8, 2)->default(0);
            $table->date('expiry_date_carried')->nullable();
            $table->decimal('manual_adjustment', 8, 2)->nullable();
            $table->text('adjustment_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_balances');
    }
};
