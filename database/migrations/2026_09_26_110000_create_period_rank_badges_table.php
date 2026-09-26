<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('period_rank_badges')) {
            Schema::create('period_rank_badges', function (Blueprint $table) {
                $table->id();
                $table->string('badge_name'); // e.g.: Daily Top Star, Weekly Champion, Monthly Legend
                $table->enum('period_type', ['daily', 'weekly', 'monthly']); // daily, weekly, monthly
                $table->enum('category', ['rich', 'charm'])->default('rich'); // rich (gifter) or charm (host)
                $table->integer('rank_position'); // 1, 2, 3...
                $table->unsignedBigInteger('min_required_coins')->default(0); // minimum coins threshold
                $table->string('badge_icon'); // public/uploads/ranks/badges/
                $table->string('avatar_frame')->nullable(); // public/uploads/ranks/frames/
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['period_type', 'category', 'rank_position']);
            });
        }

        // User earned badge history
        if (!Schema::hasTable('user_period_badges')) {
            Schema::create('user_period_badges', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('badge_id');
                $table->enum('period_type', ['daily', 'weekly', 'monthly']);
                $table->date('awarded_date'); // date earned
                $table->boolean('is_equipped')->default(true);
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('badge_id')->references('id')->on('period_rank_badges')->onDelete('cascade');
                $table->index(['user_id', 'is_equipped']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_period_badges');
        Schema::dropIfExists('period_rank_badges');
    }
};
