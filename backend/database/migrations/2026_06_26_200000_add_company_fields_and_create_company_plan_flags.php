<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('nom_legal')->nullable()->after('name');
            $table->string('adreca_facturacio', 500)->nullable()->after('nom_legal');
            $table->string('telefon', 30)->nullable()->after('adreca_facturacio');
            $table->string('email_contacte')->nullable()->after('telefon');
            $table->string('persona_contacte')->nullable()->after('email_contacte');
            $table->string('logo_url')->nullable()->after('persona_contacte');
            $table->string('favicon_url')->nullable()->after('logo_url');
        });

        Schema::create('company_plan_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('feature');
            $table->boolean('actiu')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'feature']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_plan_flags');
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['nom_legal', 'adreca_facturacio', 'telefon', 'email_contacte', 'persona_contacte', 'logo_url', 'favicon_url']);
        });
    }
};
