<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nom_producte')->default('GlobalHorario');
            $table->string('logo_url')->nullable();
            $table->string('favicon_url')->nullable();
            $table->string('color_primari')->nullable();
            $table->string('email_suport')->nullable();
            $table->text('peu_legal')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
