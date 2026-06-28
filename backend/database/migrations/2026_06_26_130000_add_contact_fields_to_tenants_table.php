<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('nom_legal')->nullable()->after('nom_intern');
            $table->string('nif', 20)->nullable()->after('nom_legal');
            $table->string('adreca_facturacio')->nullable()->after('nif');
            $table->string('telefon', 30)->nullable()->after('adreca_facturacio');
            $table->string('email_contacte')->nullable()->after('telefon');
            $table->string('persona_contacte')->nullable()->after('email_contacte');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['nom_legal', 'nif', 'adreca_facturacio', 'telefon', 'email_contacte', 'persona_contacte']);
        });
    }
};
