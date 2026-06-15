<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whitelabel_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->string('nom_producte');
            $table->string('domini_custom')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('favicon_url')->nullable();
            $table->string('color_primari')->nullable();
            $table->string('color_accent')->nullable();
            $table->string('email_remitent')->nullable();
            $table->text('peu_legal')->nullable();
            $table->string('idioma_defecte')->nullable();
            $table->boolean('ocult_powered_by')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whitelabel_configs');
    }
};
