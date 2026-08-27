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
        // 1. Add multi-angle face verification fields to kyc_verifications
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->string('face_center_image')->nullable()->after('selfie_image');
            $table->string('face_left_image')->nullable()->after('face_center_image');
            $table->string('face_right_image')->nullable()->after('face_left_image');
            $table->string('face_blink_image')->nullable()->after('face_right_image');
        });

        // 2. Add account lock/unlock fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('is_active');
            $table->string('locked_reason')->nullable()->after('is_locked');
            $table->timestamp('locked_at')->nullable()->after('locked_reason');
            $table->timestamp('unlocked_at')->nullable()->after('locked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->dropColumn([
                'face_center_image',
                'face_left_image',
                'face_right_image',
                'face_blink_image',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_locked',
                'locked_reason',
                'locked_at',
                'unlocked_at',
            ]);
        });
    }
};
