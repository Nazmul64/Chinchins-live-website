<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('streaming_settings')) {
            Schema::create('streaming_settings', function (Blueprint $table) {
                $table->id();
                // Active engine selection: 'vps_webrtc' or 'agora'
                $table->string('active_driver', 30)->default('vps_webrtc');
                
                // Agora Credentials (from Agora Console)
                $table->string('agora_project_name')->nullable();
                $table->string('agora_app_id')->nullable();
                $table->text('agora_app_certificate')->nullable();
                
                // Admin Panel Manual Temp RTC Token & Channel Override
                $table->text('agora_temp_token')->nullable();
                $table->string('agora_manual_channel')->nullable();
                
                // Audio/Video Call and Live Streaming Toggles
                $table->boolean('enable_video_call')->default(true);
                $table->boolean('enable_audio_call')->default(true);
                $table->boolean('enable_live_stream')->default(true);
                
                // Extra optional metadata
                $table->string('reverb_host')->nullable();
                $table->integer('reverb_port')->nullable();
                $table->string('reverb_scheme')->nullable()->default('https');
                $table->integer('token_expire_seconds')->default(86400); // 24 hours
                
                $table->timestamps();
            });

            // Seed default record
            DB::table('streaming_settings')->insert([
                'active_driver'         => 'vps_webrtc',
                'agora_project_name'    => 'Chinchins Live Agora Project',
                'agora_app_id'          => env('AGORA_APP_ID', ''),
                'agora_app_certificate' => env('AGORA_APP_CERTIFICATE', ''),
                'enable_video_call'     => true,
                'enable_audio_call'     => true,
                'enable_live_stream'    => true,
                'token_expire_seconds'  => 86400,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('streaming_settings');
    }
};
