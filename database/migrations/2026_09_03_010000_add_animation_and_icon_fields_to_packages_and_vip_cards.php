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
        Schema::table('coin_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('coin_packages', 'icon_url')) {
                $table->string('icon_url')->nullable()->after('currency');
            }
            if (!Schema::hasColumn('coin_packages', 'animation_url')) {
                $table->string('animation_url')->nullable()->after('icon_url');
            }
            if (!Schema::hasColumn('coin_packages', 'format')) {
                $table->string('format', 20)->default('image')->after('animation_url');
            }
        });

        Schema::table('vip_privilege_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('vip_privilege_cards', 'icon_url')) {
                $table->string('icon_url')->nullable()->after('banner_tag');
            }
            if (!Schema::hasColumn('vip_privilege_cards', 'animation_url')) {
                $table->string('animation_url')->nullable()->after('icon_url');
            }
            if (!Schema::hasColumn('vip_privilege_cards', 'bg_image_url')) {
                $table->string('bg_image_url')->nullable()->after('animation_url');
            }
            if (!Schema::hasColumn('vip_privilege_cards', 'format')) {
                $table->string('format', 20)->default('lottie')->after('bg_image_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coin_packages', function (Blueprint $table) {
            if (Schema::hasColumn('coin_packages', 'icon_url')) {
                $table->dropColumn(['icon_url', 'animation_url', 'format']);
            }
        });

        Schema::table('vip_privilege_cards', function (Blueprint $table) {
            if (Schema::hasColumn('vip_privilege_cards', 'icon_url')) {
                $table->dropColumn(['icon_url', 'animation_url', 'bg_image_url', 'format']);
            }
        });
    }
};
