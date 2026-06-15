<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->foreignId('employee_id')->constrained('employees');
            $table->string('wa_phone_hash');
            $table->enum('estat', ['pendent_verificacio', 'activa', 'revocada'])->default('pendent_verificacio');
            $table->string('codi_verificacio')->nullable();
            $table->dateTime('codi_expira_en')->nullable();
            $table->dateTime('verificada_en')->nullable();
            $table->string('wa_contact_name')->nullable();
            $table->dateTime('darrer_missatge_en')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_sessions');
    }
};
