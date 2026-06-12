<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('annual_limit', 8, 2)->nullable();
            $table->decimal('weekly_limit', 8, 2)->nullable();
            $table->enum('compensation', ['remuneration','compensatory'])->default('remuneration');
            $table->decimal('remuneration_multiplier', 5, 2)->nullable();
            $table->decimal('days_comp_per_hour', 8, 4)->nullable();
            $table->integer('comp_expiry_days')->nullable();
            $table->boolean('approval_required')->default(true);
            $table->integer('notify_limit_percent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_policies');
    }
};
