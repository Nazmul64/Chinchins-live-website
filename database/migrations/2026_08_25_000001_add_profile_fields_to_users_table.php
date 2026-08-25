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
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_id', 20)->nullable()->unique()->after('id');
            $table->string('nickname', 100)->nullable()->after('last_name');
            $table->string('avatar')->nullable()->after('nickname');
            $table->string('cover_photo')->nullable()->after('avatar');
            $table->json('gallery_images')->nullable()->after('cover_photo');
            $table->boolean('is_verified')->default(false)->after('gallery_images');
            $table->boolean('is_active')->default(true)->after('is_verified');
            $table->string('level', 20)->default('Lv1')->after('is_active');
            $table->string('country', 100)->nullable()->after('level');
            $table->string('city', 100)->nullable()->after('country');
            $table->string('gender', 20)->nullable()->after('city');
            $table->unsignedSmallInteger('age')->nullable()->after('gender');
            $table->text('introduction')->nullable()->after('age');
            $table->json('languages')->nullable()->after('introduction');
            $table->json('tags')->nullable()->after('languages');
            $table->unsignedInteger('video_call_rate')->default(1800)->after('tags');
            $table->unsignedSmallInteger('close_friends_count')->default(0)->after('video_call_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'account_id',
                'nickname',
                'avatar',
                'cover_photo',
                'gallery_images',
                'is_verified',
                'is_active',
                'level',
                'country',
                'city',
                'gender',
                'age',
                'introduction',
                'languages',
                'tags',
                'video_call_rate',
                'close_friends_count',
            ]);
        });
    }
};
