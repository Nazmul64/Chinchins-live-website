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
        if (Schema::hasTable('payment_methods')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                if (!Schema::hasColumn('payment_methods', 'bonus_coins')) {
                    $table->unsignedBigInteger('bonus_coins')->default(0)->after('rate_bdt');
                }
                if (!Schema::hasColumn('payment_methods', 'offer_tag')) {
                    $table->string('offer_tag')->nullable()->after('bonus_coins'); // e.g. "🔥 50% OFF", "30% OFF", "Best Value"
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payment_methods')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                if (Schema::hasColumn('payment_methods', 'offer_tag')) {
                    $table->dropColumn('offer_tag');
                }
                if (Schema::hasColumn('payment_methods', 'bonus_coins')) {
                    $table->dropColumn('bonus_coins');
                }
            });
        }
    }
};
