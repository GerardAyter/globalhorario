<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->unique('nom_legal');
            $table->unique('nif');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->unique('nom_legal');
            $table->unique('tax_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropUnique(['nom_legal']);
            $table->dropUnique(['nif']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropUnique(['nom_legal']);
            $table->dropUnique(['tax_id']);
        });
    }
};
