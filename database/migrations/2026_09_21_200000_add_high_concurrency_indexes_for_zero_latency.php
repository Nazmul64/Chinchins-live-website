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
        // 1. Party Room Seats Table Indexes
        if (Schema::hasTable('party_room_seats')) {
            Schema::table('party_room_seats', function (Blueprint $table) {
                if (!$this->hasIndex('party_room_seats', 'prs_room_seat_idx') &&
                    !$this->hasIndex('party_room_seats', 'party_room_seats_party_room_id_seat_index_unique')) {
                    $table->index(['party_room_id', 'seat_index'], 'prs_room_seat_idx');
                }

                if (!$this->hasIndex('party_room_seats', 'prs_user_idx') &&
                    !$this->hasIndex('party_room_seats', 'party_room_seats_user_id_index')) {
                    $table->index('user_id', 'prs_user_idx');
                }

                if (!$this->hasIndex('party_room_seats', 'prs_status_idx') &&
                    !$this->hasIndex('party_room_seats', 'party_room_seats_status_index')) {
                    $table->index('status', 'prs_status_idx');
                }
            });
        }

        // 2. Party Room Messages Table Indexes
        if (Schema::hasTable('party_room_messages')) {
            Schema::table('party_room_messages', function (Blueprint $table) {
                if (!$this->hasIndex('party_room_messages', 'prm_room_created_idx') &&
                    !$this->hasIndex('party_room_messages', 'party_room_messages_party_room_id_created_at_index')) {
                    $table->index(['party_room_id', 'created_at'], 'prm_room_created_idx');
                }

                if (!$this->hasIndex('party_room_messages', 'prm_user_idx') &&
                    !$this->hasIndex('party_room_messages', 'party_room_messages_user_id_index')) {
                    $table->index('user_id', 'prm_user_idx');
                }
            });
        }

        // 3. Wallets Table Index
        if (Schema::hasTable('wallets')) {
            Schema::table('wallets', function (Blueprint $table) {
                if (!$this->hasIndex('wallets', 'wallets_user_idx') &&
                    !$this->hasIndex('wallets', 'wallets_user_id_index') &&
                    !$this->hasIndex('wallets', 'wallets_user_id_unique')) {
                    $table->index('user_id', 'wallets_user_idx');
                }
            });
        }

        // 4. Gifts Table Indexes
        if (Schema::hasTable('gifts')) {
            Schema::table('gifts', function (Blueprint $table) {
                if (!$this->hasIndex('gifts', 'gifts_active_cat_idx') &&
                    !$this->hasIndex('gifts', 'gifts_is_active_category_index')) {
                    if (Schema::hasColumn('gifts', 'is_active') && Schema::hasColumn('gifts', 'category')) {
                        $table->index(['is_active', 'category'], 'gifts_active_cat_idx');
                    }
                }
            });
        }

        // 5. Users Table High Concurrency Indexes
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!$this->hasIndex('users', 'users_coins_idx') &&
                    !$this->hasIndex('users', 'users_coins_index')) {
                    $table->index('coins', 'users_coins_idx');
                }

                if (Schema::hasColumn('users', 'online_status') &&
                    !$this->hasIndex('users', 'users_online_status_idx') &&
                    !$this->hasIndex('users', 'users_online_status_index')) {
                    $table->index('online_status', 'users_online_status_idx');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
