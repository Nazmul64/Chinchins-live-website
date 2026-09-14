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
        // 1. Enhance users table with current_status
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'current_status')) {
                $table->string('current_status', 30)->default('available')->after('online_status');
            }
        });

        // 2. Create live_streams table
        if (!Schema::hasTable('live_streams')) {
            Schema::create('live_streams', function (Blueprint $table) {
                $table->id();
                $table->foreignId('host_id')->constrained('users')->onDelete('cascade');
                $table->string('channel_name', 150)->unique();
                $table->string('title', 200)->nullable();
                $table->string('cover_image', 500)->nullable();
                $table->string('status', 30)->default('live'); // 'live', 'ended'
                $table->unsignedInteger('viewer_count')->default(0);
                $table->unsignedInteger('total_diamonds_earned')->default(0);
                $table->text('agora_token')->nullable();
                $table->timestamp('started_at')->useCurrent();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
                $table->index('host_id');
            });
        }

        // 3. Create live_participants table
        if (!Schema::hasTable('live_participants')) {
            Schema::create('live_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_stream_id')->constrained('live_streams')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('role', 30)->default('viewer'); // 'host', 'guest', 'viewer'
                $table->boolean('is_muted')->default(false);
                $table->boolean('video_enabled')->default(true);
                $table->timestamp('joined_at')->useCurrent();
                $table->timestamp('left_at')->nullable();
                $table->timestamps();

                $table->unique(['live_stream_id', 'user_id', 'role']);
                $table->index(['live_stream_id', 'role']);
            });
        }

        // 4. Create live_join_requests table (Co-Hosting / Guest Join)
        if (!Schema::hasTable('live_join_requests')) {
            Schema::create('live_join_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_stream_id')->constrained('live_streams')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('status', 30)->default('pending'); // 'pending', 'accepted', 'rejected', 'cancelled'
                $table->timestamps();

                $table->index(['live_stream_id', 'status']);
                $table->index(['user_id', 'status']);
            });
        }

        // 5. Create live_messages table
        if (!Schema::hasTable('live_messages')) {
            Schema::create('live_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_stream_id')->constrained('live_streams')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('message')->nullable();
                $table->string('type', 30)->default('text'); // 'text', 'gift', 'system', 'audio', 'join'
                $table->foreignId('gift_id')->nullable()->constrained('gifts')->nullOnDelete();
                $table->unsignedInteger('coin_amount')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['live_stream_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_messages');
        Schema::dropIfExists('live_join_requests');
        Schema::dropIfExists('live_participants');
        Schema::dropIfExists('live_streams');
        
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'current_status')) {
                $table->dropColumn('current_status');
            }
        });
    }
};
