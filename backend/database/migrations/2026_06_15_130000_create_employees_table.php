<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('workplace_id')->nullable()->constrained('workplaces');

            $table->string('nom');
            $table->string('cognoms');
            $table->string('dni_nie');
            $table->string('nss')->nullable();
            $table->date('data_naixement')->nullable();
            $table->string('email');
            $table->string('telefon')->nullable();

            $table->foreignId('politica_absencia_id')->nullable()->constrained('policy_absences');
            $table->foreignId('politica_horari_id')->nullable()->constrained('policy_schedules');
            $table->foreignId('torn_id')->nullable()->constrained('shifts');

            $table->decimal('percentatge_jornada', 5, 2)->default(100);
            $table->boolean('geoloc_requerida')->default(false);
            $table->string('whatsapp_phone_hash')->nullable();
            $table->boolean('whatsapp_verificat')->default(false);
            $table->boolean('actiu')->default(true);
            $table->date('data_alta');
            $table->date('data_baixa')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'dni_nie']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
