<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. messages table
        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->string('call_session_id')->nullable()->index();
                $table->text('message');
                $table->string('type')->default('text');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        } else {
            Schema::table('messages', function (Blueprint $table) {
                if (!Schema::hasColumn('messages', 'receiver_id')) {
                    $table->unsignedBigInteger('receiver_id')->nullable()->after('sender_id')->index();
                }
                if (!Schema::hasColumn('messages', 'call_session_id')) {
                    $table->string('call_session_id')->nullable()->after('receiver_id')->index();
                }
                if (!Schema::hasColumn('messages', 'type')) {
                    $table->string('type')->default('text')->after('message');
                }
                if (!Schema::hasColumn('messages', 'is_read')) {
                    $table->boolean('is_read')->default(false)->after('type');
                }
            });
        }

        // 2. user_gifts table
        if (!Schema::hasTable('user_gifts')) {
            Schema::create('user_gifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('gift_id')->constrained('gifts')->onDelete('cascade');
                $table->string('call_session_id')->nullable()->index();
                $table->integer('coin_amount')->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table('user_gifts', function (Blueprint $table) {
                if (!Schema::hasColumn('user_gifts', 'receiver_id')) {
                    $table->unsignedBigInteger('receiver_id')->nullable()->after('sender_id')->index();
                }
                if (!Schema::hasColumn('user_gifts', 'coin_amount')) {
                    $table->integer('coin_amount')->default(0)->after('call_session_id');
                }
                if (Schema::hasColumn('user_gifts', 'call_session_id')) {
                    // Make call_session_id varchar/string if needed
                    $table->string('call_session_id')->nullable()->change();
                }
            });

            // Backfill receiver_id and coin_amount from user_id and total_coins if available
            try {
                if (Schema::hasColumn('user_gifts', 'user_id') && Schema::hasColumn('user_gifts', 'receiver_id')) {
                    DB::statement("UPDATE user_gifts SET receiver_id = user_id WHERE receiver_id IS NULL");
                }
                if (Schema::hasColumn('user_gifts', 'total_coins') && Schema::hasColumn('user_gifts', 'coin_amount')) {
                    DB::statement("UPDATE user_gifts SET coin_amount = total_coins WHERE coin_amount = 0 OR coin_amount IS NULL");
                }
            } catch (\Throwable $e) {}
        }

        // 3. users table: ensure wallet_balance & received_coins exist for fast balance operations
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'wallet_balance')) {
                $table->unsignedBigInteger('wallet_balance')->default(0)->after('coins');
            }
            if (!Schema::hasColumn('users', 'received_coins')) {
                $table->unsignedBigInteger('received_coins')->default(0)->after('wallet_balance');
            }
        });

        // Initialize user wallet_balance from existing coins column
        try {
            DB::statement("UPDATE users SET wallet_balance = coins WHERE wallet_balance = 0 AND coins > 0");
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive down
    }
};
