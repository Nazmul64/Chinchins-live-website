<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('call_sessions')) {
            Schema::table('call_sessions', function (Blueprint $table) {
                if (!Schema::hasColumn('call_sessions', 'answered_at')) {
                    $table->timestamp('answered_at')->nullable()->after('started_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('call_sessions')) {
            Schema::table('call_sessions', function (Blueprint $table) {
                if (Schema::hasColumn('call_sessions', 'answered_at')) {
                    $table->dropColumn('answered_at');
                }
            });
        }
    }
};
