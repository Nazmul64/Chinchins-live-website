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
        if (!Schema::hasTable('calls')) {
            Schema::create('calls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('caller_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
                $table->enum('call_type', ['audio', 'video'])->default('video');
                $table->enum('status', [
                    'calling',
                    'ringing',
                    'accepted',
                    'rejected',
                    'busy',
                    'ended',
                    'missed',
                    'cancelled'
                ])->default('calling');
                $table->string('room_id', 100)->unique();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('answered_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->foreignId('ended_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                // Indexing for ultra-fast query & signaling lookups
                $table->index(['caller_id', 'status']);
                $table->index(['receiver_id', 'status']);
                $table->index('room_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
