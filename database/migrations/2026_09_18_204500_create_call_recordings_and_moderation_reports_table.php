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
        if (!Schema::hasTable('call_recordings')) {
            Schema::create('call_recordings', function (Blueprint $table) {
                $table->id();
                $table->string('call_session_id', 100)->index();
                $table->unsignedBigInteger('caller_id')->nullable()->index();
                $table->unsignedBigInteger('receiver_id')->nullable()->index();
                $table->enum('call_type', ['video', 'audio'])->default('video');
                $table->integer('duration_seconds')->default(0);
                $table->string('recording_url', 1000)->nullable();
                $table->json('snapshot_urls')->nullable();
                $table->string('engine', 50)->default('vps_webrtc'); // vps_webrtc, agora
                $table->string('agora_sid', 100)->nullable();
                $table->string('agora_resource_id', 255)->nullable();
                $table->enum('status', ['recording', 'processing', 'completed', 'failed', 'reported'])->default('completed');
                $table->timestamps();

                $table->foreign('caller_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('receiver_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('call_moderation_reports')) {
            Schema::create('call_moderation_reports', function (Blueprint $table) {
                $table->id();
                $table->string('call_session_id', 100)->index();
                $table->unsignedBigInteger('reporter_id')->index();
                $table->unsignedBigInteger('reported_user_id')->index();
                $table->string('reason', 255)->nullable();
                $table->text('description')->nullable();
                $table->json('evidence_snapshots')->nullable();
                $table->string('evidence_video_url', 1000)->nullable();
                $table->enum('status', ['pending', 'investigating', 'warning_issued', 'banned', 'dismissed'])->default('pending');
                $table->text('admin_notes')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->foreign('reporter_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('reported_user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_moderation_reports');
        Schema::dropIfExists('call_recordings');
    }
};
