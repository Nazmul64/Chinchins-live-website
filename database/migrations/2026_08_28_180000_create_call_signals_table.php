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
        if (!Schema::hasTable('call_signals')) {
            Schema::create('call_signals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('call_session_id')->nullable()->index();
                $table->string('channel_name')->nullable()->index();
                $table->unsignedBigInteger('sender_id')->index();
                $table->unsignedBigInteger('receiver_id')->nullable()->index();
                $table->string('type')->default('offer'); // offer, answer, candidate, ping, bye
                $table->longText('payload'); // SDP JSON or ICE candidate JSON
                $table->boolean('is_read')->default(false)->index();
                $table->timestamps();

                $table->index(['call_session_id', 'receiver_id', 'is_read']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_signals');
    }
};
