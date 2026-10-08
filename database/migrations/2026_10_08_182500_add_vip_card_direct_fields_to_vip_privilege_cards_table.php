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
            if (!Schema::hasColumn('vip_privilege_cards', 'price')) {
                $table->decimal('price', 10, 2)->default(300.00)->nullable()->after('name');
            }
            if (!Schema::hasColumn('vip_privilege_cards', 'diamonds_reward')) {
                $table->unsignedBigInteger('diamonds_reward')->default(32940)->nullable()->after('price');
            }
            if (!Schema::hasColumn('vip_privilege_cards', 'cost_diamonds')) {
                $table->unsignedBigInteger('cost_diamonds')->default(300)->nullable()->after('diamonds_reward');
            }
            if (!Schema::hasColumn('vip_privilege_cards', 'daily_checkin_diamonds')) {
                $table->unsignedBigInteger('daily_checkin_diamonds')->default(26330)->nullable()->after('cost_diamonds');
            }
            if (!Schema::hasColumn('vip_privilege_cards', 'perks')) {
                $table->string('perks')->default('3 Perks')->nullable()->after('daily_checkin_diamonds');
            }
            if (!Schema::hasColumn('vip_privilege_cards', 'outfits')) {
                $table->string('outfits')->default('VIP Outfits')->nullable()->after('perks');
            }
            if (!Schema::hasColumn('vip_privilege_cards', 'validity_days')) {
                $table->unsignedInteger('validity_days')->default(30)->nullable()->after('outfits');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vip_privilege_cards', function (Blueprint $table) {
            $cols = ['price', 'diamonds_reward', 'cost_diamonds', 'daily_checkin_diamonds', 'perks', 'outfits', 'validity_days'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('vip_privilege_cards', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
