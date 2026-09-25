<?php

namespace App\Jobs;

use App\Models\CallSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogCallSessionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $callerId;
    public int $receiverId;
    public array $sessionAttributes;

    /**
     * Create a new job instance.
     */
    public function __construct(int $callerId, int $receiverId, array $sessionAttributes = [])
    {
        $this->callerId = $callerId;
        $this->receiverId = $receiverId;
        $this->sessionAttributes = $sessionAttributes;
    }

    /**
     * Execute the job asynchronously.
     */
    public function handle(): void
    {
        try {
            $caller = User::find($this->callerId);
            $receiver = User::find($this->receiverId);

            if (!$caller || !$receiver) {
                return;
            }

            $channelName = $this->sessionAttributes['channel_name'] 
                ?? $this->sessionAttributes['channel'] 
                ?? ('call_video_' . $this->callerId . '_' . $this->receiverId . '_' . time());

            $callType = $this->sessionAttributes['call_type'] ?? 'video';
            $ratePerMinute = (int) ($this->sessionAttributes['rate_per_minute'] ?? 100);
            $isFreeTrial = (bool) ($this->sessionAttributes['is_free_trial'] ?? false);
            $freeDuration = (int) ($this->sessionAttributes['free_duration_seconds'] ?? 0);

            CallSession::create([
                'caller_id'             => $this->callerId,
                'receiver_id'           => $this->receiverId,
                'channel_name'          => $channelName,
                'call_type'             => $callType,
                'status'                => $this->sessionAttributes['status'] ?? 'ringing',
                'rate_per_minute'       => $ratePerMinute,
                'is_free_trial'         => $isFreeTrial,
                'is_caller_free'        => (bool) ($this->sessionAttributes['is_caller_free'] ?? false),
                'charged_user_id'       => $this->callerId,
                'free_duration_seconds' => $freeDuration,
                'is_random_match'       => (bool) ($this->sessionAttributes['is_random_match'] ?? false),
            ]);
        } catch (\Throwable $e) {
            Log::error("LogCallSessionJob failed: " . $e->getMessage());
        }
    }
}
