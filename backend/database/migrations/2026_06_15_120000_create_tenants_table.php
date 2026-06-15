<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nom_intern')->unique();
            $table->enum('pla', ['starter','pro','enterprise'])->default('starter');
            $table->integer('max_empleats')->default(0);
            $table->boolean('actiu')->default(true);
            $table->timestamp('data_alta')->useCurrent();
            $table->timestamp('data_baixa')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
