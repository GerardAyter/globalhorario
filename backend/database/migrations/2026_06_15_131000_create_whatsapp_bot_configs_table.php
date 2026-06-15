<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_bot_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->foreignId('company_id')->constrained('companies')->unique();
            $table->string('waba_id');
            $table->string('phone_number_id');
            $table->string('webhook_verify_token');
            $table->boolean('actiu')->default(true);
            $table->string('idioma_bot')->default('ca');
            $table->text('missatge_benvinguda')->nullable();
            $table->text('missatge_entrada_ok')->nullable();
            $table->text('missatge_sortida_ok')->nullable();
            $table->text('missatge_error_fora_radi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_bot_configs');
    }
};
