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
        Schema::table('vip_privilege_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('vip_privilege_cards', 'original_price_bdt')) {
                $table->decimal('original_price_bdt', 10, 2)->nullable()->after('price_bdt');
            }
            if (!Schema::hasColumn('vip_privilege_cards', 'category_name')) {
                $table->string('category_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('vip_privilege_cards', 'instant_reward_text')) {
                $table->string('instant_reward_text')->nullable()->after('instant_reward_coins');
            }
            if (!Schema::hasColumn('vip_privilege_cards', 'daily_checkin_text')) {
                $table->string('daily_checkin_text')->nullable()->after('daily_checkin_total_coins');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vip_privilege_cards', function (Blueprint $table) {
            if (Schema::hasColumn('vip_privilege_cards', 'original_price_bdt')) {
                $table->dropColumn('original_price_bdt');
            }
            if (Schema::hasColumn('vip_privilege_cards', 'category_name')) {
                $table->dropColumn('category_name');
            }
            if (Schema::hasColumn('vip_privilege_cards', 'instant_reward_text')) {
                $table->dropColumn('instant_reward_text');
            }
            if (Schema::hasColumn('vip_privilege_cards', 'daily_checkin_text')) {
                $table->dropColumn('daily_checkin_text');
            }
        });
    }
};
