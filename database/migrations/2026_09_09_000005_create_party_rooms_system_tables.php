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
        // 1. Party Rooms Table
        Schema::create('party_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_id', 32)->unique()->index();
            $table->foreignId('host_id')->constrained('users')->onDelete('cascade');
            $table->string('room_title', 150)->default('My Live Fun Hangout 🥳✨');
            $table->enum('room_type', ['voice', 'video'])->default('voice');
            $table->string('topic_tag', 64)->default('Singing');
            $table->string('room_cover')->nullable();
            $table->string('background_image')->nullable();
            $table->string('channel_name', 150)->unique()->index();
            $table->unsignedTinyInteger('max_seats')->default(10);
            $table->unsignedInteger('coin_rate_per_minute')->default(100);
            $table->decimal('host_commission_percentage', 5, 2)->default(50.00);
            $table->decimal('admin_commission_percentage', 5, 2)->default(50.00);
            $table->unsignedBigInteger('total_earned_coins')->default(0);
            $table->unsignedBigInteger('total_admin_earned_coins')->default(0);
            $table->enum('status', ['active', 'ended', 'paused'])->default('active')->index();
            $table->boolean('is_locked')->default(false);
            $table->string('room_password', 32)->nullable();
            $table->text('announcement')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        // 2. Party Room Seats (Up to 10 seats: Seat 1 is Host, Seats 2-10 are Guests)
        Schema::create('party_room_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_room_id')->constrained('party_rooms')->onDelete('cascade');
            $table->unsignedTinyInteger('seat_index')->index(); // 1 = Host, 2..10 = Guests
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('role', ['host', 'co_host', 'speaker', 'guest'])->default('guest');
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_video_muted')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->unsignedBigInteger('coins_spent')->default(0);
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_billed_at')->nullable();
            $table->enum('status', ['occupied', 'empty', 'reserved'])->default('empty');
            $table->timestamps();

            $table->unique(['party_room_id', 'seat_index']);
        });

        // 3. Party Room Members / Audience
        Schema::create('party_room_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_room_id')->constrained('party_rooms')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['host', 'speaker', 'audience'])->default('audience');
            $table->unsignedBigInteger('total_coins_spent')->default(0);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('last_active_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->enum('status', ['active', 'left', 'kicked', 'banned'])->default('active');
            $table->timestamps();

            $table->index(['party_room_id', 'user_id']);
        });

        // 4. In-Room Chat & Image Messages
        Schema::create('party_room_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_room_id')->constrained('party_rooms')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['text', 'image', 'gift', 'system', 'seat_join', 'seat_leave'])->default('text');
            $table->text('message')->nullable();
            $table->string('image_url')->nullable();
            $table->unsignedBigInteger('gift_id')->nullable();
            $table->unsignedInteger('gift_count')->default(1);
            $table->unsignedBigInteger('coins_amount')->default(0);
            $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('extra_data')->nullable();
            $table->timestamps();

            $table->index(['party_room_id', 'created_at']);
        });

        // 5. Seat Invitations
        Schema::create('party_room_seat_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_room_id')->constrained('party_rooms')->onDelete('cascade');
            $table->foreignId('host_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('seat_index');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled', 'expired'])->default('pending');
            $table->timestamps();

            $table->index(['party_room_id', 'user_id', 'status']);
        });

        // 6. Party Room System Settings
        Schema::create('party_room_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('default_voice_rate_per_minute')->default(100);
            $table->unsignedInteger('default_video_rate_per_minute')->default(100);
            $table->decimal('host_commission_percentage', 5, 2)->default(50.00);
            $table->decimal('admin_commission_percentage', 5, 2)->default(50.00);
            $table->unsignedTinyInteger('max_guests_per_room')->default(10);
            $table->boolean('is_party_room_enabled')->default(true);
            $table->json('available_topic_tags')->nullable();
            $table->text('default_announcement')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('party_room_settings');
        Schema::dropIfExists('party_room_seat_invitations');
        Schema::dropIfExists('party_room_messages');
        Schema::dropIfExists('party_room_members');
        Schema::dropIfExists('party_room_seats');
        Schema::dropIfExists('party_rooms');
    }
};
