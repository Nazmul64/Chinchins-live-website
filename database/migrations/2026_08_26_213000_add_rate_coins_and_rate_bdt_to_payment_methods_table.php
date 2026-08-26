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
                if (!Schema::hasColumn('payment_methods', 'rate_coins')) {
                    $table->unsignedBigInteger('rate_coins')->default(500)->after('max_deposit');
                }
                if (!Schema::hasColumn('payment_methods', 'rate_bdt')) {
                    $table->decimal('rate_bdt', 10, 2)->default(50.00)->after('rate_coins');
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
                if (Schema::hasColumn('payment_methods', 'rate_bdt')) {
                    $table->dropColumn('rate_bdt');
                }
                if (Schema::hasColumn('payment_methods', 'rate_coins')) {
                    $table->dropColumn('rate_coins');
                }
            });
        }
    }
};
