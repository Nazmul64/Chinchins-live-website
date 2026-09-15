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
        if (!Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_one')->constrained('users')->onDelete('cascade');
                $table->foreignId('user_two')->constrained('users')->onDelete('cascade');
                $table->text('last_message')->nullable();
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();

                $table->unique(['user_one', 'user_two']);
            });
        } else {
            Schema::table('conversations', function (Blueprint $table) {
                if (!Schema::hasColumn('conversations', 'user_one')) {
                    $table->foreignId('user_one')->nullable()->after('id')->constrained('users')->onDelete('cascade');
                }
                if (!Schema::hasColumn('conversations', 'user_two')) {
                    $table->foreignId('user_two')->nullable()->after('user_one')->constrained('users')->onDelete('cascade');
                }
                if (!Schema::hasColumn('conversations', 'last_message')) {
                    $table->text('last_message')->nullable()->after('user_two');
                }
                if (!Schema::hasColumn('conversations', 'last_message_at')) {
                    $table->timestamp('last_message_at')->nullable()->after('last_message');
                }
            });
        }

        if (!Schema::hasTable('direct_messages')) {
            Schema::create('direct_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
                $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
                $table->text('message')->nullable();
                $table->string('attachment_path')->nullable();
                $table->enum('type', ['text', 'image'])->default('text');
                $table->boolean('is_read')->default(false);
                $table->timestamps();

                $table->index(['conversation_id', 'created_at']);
                $table->index(['sender_id', 'receiver_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direct_messages');
    }
};
