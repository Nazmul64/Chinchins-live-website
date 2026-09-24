<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Helper to check if an index exists on a table.
     */
    protected function hasIndex(string $table, string $indexName): bool
    {
        try {
            $indexes = Schema::getIndexes($table);
            foreach ($indexes as $index) {
                if (($index['name'] ?? '') === $indexName) {
                    return true;
                }
            }
        } catch (\Throwable $e) {}
        return false;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Party Room Messages Table Indexes
        if (Schema::hasTable('party_room_messages')) {
            Schema::table('party_room_messages', function (Blueprint $table) {
                if (!$this->hasIndex('party_room_messages', 'prm_room_created_idx')) {
                    $table->index(['party_room_id', 'created_at'], 'prm_room_created_idx');
                }
            });
        }

        // 2. Live Streams Table Indexes
        if (Schema::hasTable('live_streams')) {
            Schema::table('live_streams', function (Blueprint $table) {
                if (Schema::hasColumn('live_streams', 'host_id') && !$this->hasIndex('live_streams', 'ls_status_host_idx')) {
                    $table->index(['status', 'host_id'], 'ls_status_host_idx');
                }
                if (Schema::hasColumn('live_streams', 'user_id') && !$this->hasIndex('live_streams', 'ls_status_user_idx')) {
                    $table->index(['status', 'user_id'], 'ls_status_user_idx');
                }
            });
        }

        // 3. Wallets Table Index
        if (Schema::hasTable('wallets')) {
            Schema::table('wallets', function (Blueprint $table) {
                if (!$this->hasIndex('wallets', 'wallets_user_id_idx') &&
                    !$this->hasIndex('wallets', 'wallets_user_idx') &&
                    !$this->hasIndex('wallets', 'wallets_user_id_unique')) {
                    $table->index('user_id', 'wallets_user_id_idx');
                }
            });
        }

        // 4. Coin Transactions Table Indexes
        if (Schema::hasTable('coin_transactions')) {
            Schema::table('coin_transactions', function (Blueprint $table) {
                if (!$this->hasIndex('coin_transactions', 'ctx_user_created_idx')) {
                    $table->index(['user_id', 'created_at'], 'ctx_user_created_idx');
                }
            });
        }

        // 5. Chat Messages Table Indexes
        if (Schema::hasTable('chat_messages')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                if (!$this->hasIndex('chat_messages', 'cm_sender_receiver_idx')) {
                    $table->index(['sender_id', 'receiver_id'], 'cm_sender_receiver_idx');
                }
                if (Schema::hasColumn('chat_messages', 'conversation_id') && !$this->hasIndex('chat_messages', 'cm_conv_created_idx')) {
                    $table->index(['conversation_id', 'created_at'], 'cm_conv_created_idx');
                }
            });
        }

        // 6. Call Sessions Table Indexes
        if (Schema::hasTable('call_sessions')) {
            Schema::table('call_sessions', function (Blueprint $table) {
                if (!$this->hasIndex('call_sessions', 'cs_caller_receiver_status_idx')) {
                    $table->index(['caller_id', 'receiver_id', 'status'], 'cs_caller_receiver_status_idx');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op on rollback
    }
};
