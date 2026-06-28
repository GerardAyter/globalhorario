<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whitelabel_configs', function (Blueprint $table) {
            $table->string('nom_producte')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('whitelabel_configs', function (Blueprint $table) {
            $table->string('nom_producte')->nullable(false)->change();
        });
    }
};
