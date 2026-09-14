<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Conversations Table
        if (!Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_group')->default(false)->index();
                $table->string('title')->nullable();
                $table->unsignedBigInteger('last_message_id')->nullable()->index();
                $table->timestamps();
            });
        }

        // 2. Conversation Participants Pivot Table
        if (!Schema::hasTable('conversation_participants')) {
            Schema::create('conversation_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('joined_at')->useCurrent();
                $table->timestamps();

                $table->unique(['conversation_id', 'user_id']);
            });
        }

        // 3. Messages Table (Core Messaging with Call & Idempotency Metadata)
        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_uuid')->nullable()->unique();
                $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
                $table->string('call_id')->nullable()->index();
                $table->boolean('sent_during_call')->default(false)->index();
                $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
                $table->text('message');
                $table->string('type', 30)->default('text');
                $table->string('media_url')->nullable();
                $table->boolean('is_read')->default(false)->index();
                $table->timestamps();

                $table->index(['conversation_id', 'created_at']);
            });
        } else {
            Schema::table('messages', function (Blueprint $table) {
                if (!Schema::hasColumn('messages', 'client_uuid')) {
                    $table->uuid('client_uuid')->nullable()->after('id')->unique();
                }
                if (!Schema::hasColumn('messages', 'call_id')) {
                    $table->string('call_id')->nullable()->after('conversation_id')->index();
                }
                if (!Schema::hasColumn('messages', 'sent_during_call')) {
                    $table->boolean('sent_during_call')->default(false)->after('call_id')->index();
                }
            });
        }

        // 4. Live Rooms Table
        if (!Schema::hasTable('live_rooms')) {
            Schema::create('live_rooms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
                $table->string('channel_name')->unique();
                $table->string('title');
                $table->enum('type', ['video', 'audio'])->default('video');
                $table->enum('status', ['active', 'ended'])->default('active')->index();
                $table->unsignedInteger('viewer_count')->default(0);
                $table->unsignedBigInteger('total_diamonds_earned')->default(0);
                $table->string('cover_image')->nullable();
                $table->text('stream_token')->nullable();
                $table->timestamp('started_at')->useCurrent();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();
            });
        }

        // 5. Enhance live_participants table if needed
        if (Schema::hasTable('live_participants')) {
            Schema::table('live_participants', function (Blueprint $table) {
                if (!Schema::hasColumn('live_participants', 'live_room_id')) {
                    $table->unsignedBigInteger('live_room_id')->nullable()->after('id')->index();
                }
                if (!Schema::hasColumn('live_participants', 'join_request_status')) {
                    $table->enum('join_request_status', ['none', 'pending', 'accepted', 'rejected'])->default('none')->after('role');
                }
            });
        }

        // 6. Enhance live_messages table if needed
        if (Schema::hasTable('live_messages')) {
            Schema::table('live_messages', function (Blueprint $table) {
                if (!Schema::hasColumn('live_messages', 'live_room_id')) {
                    $table->unsignedBigInteger('live_room_id')->nullable()->after('id')->index();
                }
            });
        }

        // 7. Wallets Table
        if (!Schema::hasTable('wallets')) {
            Schema::create('wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('balance')->default(0);
                $table->unsignedBigInteger('earnings')->default(0);
                $table->timestamps();
            });
        }

        // 8. Enhance Gifts Table
        Schema::table('gifts', function (Blueprint $table) {
            if (!Schema::hasColumn('gifts', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (!Schema::hasColumn('gifts', 'animation_asset_url')) {
                $table->string('animation_asset_url')->nullable()->after('icon_url');
            }
            if (!Schema::hasColumn('gifts', 'animation_type')) {
                $table->enum('animation_type', ['svg', 'lottie'])->default('svg')->after('animation_asset_url');
            }
        });

        // 9. Enhance Gift Transactions Table
        Schema::table('gift_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('gift_transactions', 'idempotency_key')) {
                $table->string('idempotency_key')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('gift_transactions', 'live_room_id')) {
                $table->unsignedBigInteger('live_room_id')->nullable()->after('receiver_id')->index();
            }
            if (!Schema::hasColumn('gift_transactions', 'quantity')) {
                $table->unsignedInteger('quantity')->default(1)->after('gift_id');
            }
            if (!Schema::hasColumn('gift_transactions', 'total_coins')) {
                $table->unsignedBigInteger('total_coins')->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('gift_transactions', 'status')) {
                $table->enum('status', ['completed', 'failed'])->default('completed')->after('total_coins');
            }
        });

        // 10. Enhance app_settings with description & default seeds
        Schema::table('app_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('app_settings', 'description')) {
                $table->string('description')->nullable()->after('value');
            }
        });

        // Ensure show_offline_users setting exists
        if (DB::table('app_settings')->where('key', 'show_offline_users')->doesntExist()) {
            DB::table('app_settings')->insert([
                'key'         => 'show_offline_users',
                'value'       => 'false',
                'description' => 'Display offline users in discovery lists when true',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe reversible drops
    }
};
