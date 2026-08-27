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
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->string('face_video')->nullable()->after('face_blink_image');
            $table->string('video_duration')->nullable()->after('face_video');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->dropColumn(['face_video', 'video_duration']);
        });
    }
};
