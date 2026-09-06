<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('streaming_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('streaming_settings', 'agora_temp_token')) {
                $table->text('agora_temp_token')->nullable()->after('agora_app_certificate');
            }
            if (!Schema::hasColumn('streaming_settings', 'agora_manual_channel')) {
                $table->string('agora_manual_channel')->nullable()->after('agora_temp_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('streaming_settings', function (Blueprint $table) {
            if (Schema::hasColumn('streaming_settings', 'agora_manual_channel')) {
                $table->dropColumn('agora_manual_channel');
            }
            if (Schema::hasColumn('streaming_settings', 'agora_temp_token')) {
                $table->dropColumn('agora_temp_token');
            }
        });
    }
};
