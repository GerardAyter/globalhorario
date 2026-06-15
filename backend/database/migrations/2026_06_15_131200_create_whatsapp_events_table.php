<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->foreignId('sessio_id')->constrained('whatsapp_sessions');
            $table->enum('direccio', ['entrada', 'sortida']);
            $table->enum('tipus_msg', ['text', 'location', 'button', 'template']);
            $table->json('payload_raw');
            $table->enum('intencio', ['fichar_entrada','fichar_sortida','consultar_saldo','sollicitar_vacances','ajuda','desconegut'])->nullable();
            $table->decimal('geoloc_lat', 10, 7)->nullable();
            $table->decimal('geoloc_lng', 10, 7)->nullable();
            $table->foreignId('time_entry_id')->nullable()->constrained('time_entries');
            $table->enum('estat_proc', ['ok','error','pendent'])->default('pendent');
            $table->text('error_msg')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // immutable: no updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_events');
    }
};
