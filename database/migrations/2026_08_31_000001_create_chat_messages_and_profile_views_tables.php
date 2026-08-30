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
        // 1. Profile Views Table (Tracks profile visits & auto-call / callback triggers)
        if (!Schema::hasTable('profile_views')) {
            Schema::create('profile_views', function (Blueprint $table) {
                $table->id();
                $table->foreignId('viewer_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('host_id')->constrained('users')->onDelete('cascade');
                $table->boolean('auto_call_triggered')->default(false);
                $table->boolean('callback_requested')->default(false);
                $table->string('status')->default('viewed'); // viewed, auto_called, callback_pending, connected
                $table->timestamp('viewed_at')->nullable();
                $table->timestamps();

                $table->index(['viewer_id', 'host_id']);
                $table->index(['host_id', 'created_at']);
            });
        }

        // 2. Chat Messages Table (Supports Text, Voice Audio, Image, Video Call logs)
        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
                $table->string('type')->default('text'); // text, image, voice, video_call, audio_call
                $table->text('message')->nullable();
                $table->string('media_url')->nullable();
                $table->integer('duration')->default(0); // For voice notes or call logs (in seconds)
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->boolean('is_free')->default(true);
                $table->integer('coin_cost')->default(0);
                $table->timestamps();

                $table->index(['sender_id', 'receiver_id']);
                $table->index(['receiver_id', 'is_read']);
                $table->index('created_at');
            });
        }

        // 3. Add Free Message Count and Settings to users table if missing
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'free_messages_used')) {
                $table->integer('free_messages_used')->default(0)->after('coins');
            }
            if (!Schema::hasColumn('users', 'free_messages_limit')) {
                $table->integer('free_messages_limit')->default(5)->after('free_messages_used');
            }
            if (!Schema::hasColumn('users', 'is_busy')) {
                $table->boolean('is_busy')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('users', 'auto_call_enabled')) {
                $table->boolean('auto_call_enabled')->default(true)->after('is_busy');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('profile_views');
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'free_messages_used')) {
                $table->dropColumn('free_messages_used');
            }
            if (Schema::hasColumn('users', 'free_messages_limit')) {
                $table->dropColumn('free_messages_limit');
            }
            if (Schema::hasColumn('users', 'is_busy')) {
                $table->dropColumn('is_busy');
            }
            if (Schema::hasColumn('users', 'auto_call_enabled')) {
                $table->dropColumn('auto_call_enabled');
            }
        });
    }
};
