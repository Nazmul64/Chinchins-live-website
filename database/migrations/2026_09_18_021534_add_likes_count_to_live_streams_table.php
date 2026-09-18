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
        Schema::table('live_streams', function (Blueprint $table) {
            if (!Schema::hasColumn('live_streams', 'likes_count')) {
                $table->unsignedBigInteger('likes_count')->default(0)->after('viewer_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_streams', function (Blueprint $table) {
            if (Schema::hasColumn('live_streams', 'likes_count')) {
                $table->dropColumn('likes_count');
            }
        });
    }
};
