<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('daily_rewards')) {
            Schema::create('daily_rewards', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('day_number')->unique(); // Day 1 to Day 7
                $table->integer('reward_coins')->default(50);         // +50, +100
                $table->string('icon_image')->nullable();             // uploads/claim/icon.png
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_daily_claims')) {
            Schema::create('user_daily_claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedTinyInteger('day_claimed'); // 1, 2, ..., 7
                $table->integer('coins_awarded');
                $table->timestamp('claimed_at');
                $table->timestamps();

                $table->index(['user_id', 'claimed_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_daily_claims');
        Schema::dropIfExists('daily_rewards');
    }
};
