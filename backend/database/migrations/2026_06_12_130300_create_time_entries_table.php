<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('clock_in_at')->nullable();
            $table->timestamp('clock_out_at')->nullable();
            $table->string('source')->nullable();
            $table->json('clock_in_geo')->nullable();
            $table->json('clock_out_geo')->nullable();
            $table->integer('distance_meters')->nullable();
            $table->boolean('within_radius')->default(false);
            $table->enum('status', ['pending', 'validated', 'rejected'])->default('pending');
            $table->uuid('validated_by')->nullable();
            $table->text('validator_notes')->nullable();
            $table->string('integrity_hash')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
