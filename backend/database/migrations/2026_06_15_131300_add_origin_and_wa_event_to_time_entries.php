<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('time_entries', 'origin')) {
                $table->enum('origin', ['app','web','nfc','whatsapp','kiosk'])->default('app')->after('tenant_id');
            }
            if (! Schema::hasColumn('time_entries', 'wa_event_id')) {
                $table->unsignedBigInteger('wa_event_id')->nullable()->after('integrity_hash');
            }
        });

        // add foreign key to whatsapp_events if table exists
        if (Schema::hasTable('whatsapp_events')) {
            Schema::table('time_entries', function (Blueprint $table) {
                $table->foreign('wa_event_id')->references('id')->on('whatsapp_events')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            if (Schema::hasColumn('time_entries', 'wa_event_id')) {
                $table->dropForeign(['wa_event_id']);
                $table->dropColumn('wa_event_id');
            }
            if (Schema::hasColumn('time_entries', 'origin')) {
                $table->dropColumn('origin');
            }
        });
    }
};
