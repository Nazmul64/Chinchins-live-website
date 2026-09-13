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
        if (!Schema::hasTable('user_follows')) {
            Schema::create('user_follows', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('The user being followed');
                $table->unsignedBigInteger('follower_id')->comment('The user who is following');
                $table->string('source')->default('call')->comment('Source: call, profile, match, party_room');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('follower_id')->references('id')->on('users')->onDelete('cascade');

                $table->unique(['user_id', 'follower_id'], 'uniq_user_follower');
                $table->index('user_id');
                $table->index('follower_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_follows');
    }
};
