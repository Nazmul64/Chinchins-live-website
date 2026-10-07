<?php

namespace App\Console\Commands;

use App\Events\CallCancelled;
use App\Events\CallEnded;
use App\Models\Call;
use App\Models\CallSession;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckCallTimeouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'call:timeout-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enforce 45-second timeout on ringing calls without an answered_at timestamp and mark as missed/cancelled';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $timeoutLimit = now()->subSeconds(45);

        // 1. Process timed-out CallSession records
        $staleSessions = CallSession::whereIn('status', ['ringing', 'initiated'])
            ->whereNull('answered_at')
            ->where('created_at', '<=', $timeoutLimit)
            ->get();

        $count = 0;

        foreach ($staleSessions as $session) {
            $session->update([
                'status'   => 'missed',
                'ended_at' => now(),
            ]);

            // Release user busy states if needed
            if ($session->caller_id) {
                Cache::forget("user:{$session->caller_id}:is_busy");
                User::where('id', $session->caller_id)->where('online_status', 'in_call')->update(['online_status' => 'online', 'is_busy' => false]);
            }
            if ($session->receiver_id) {
                Cache::forget("user:{$session->receiver_id}:is_busy");
                User::where('id', $session->receiver_id)->where('online_status', 'in_call')->update(['online_status' => 'online', 'is_busy' => false]);
            }

            // Sync calls table
            Call::where('id', $session->id)
                ->orWhere('room_id', $session->channel_name)
                ->whereIn('status', ['calling', 'ringing'])
                ->update([
                    'status'   => 'missed',
                    'ended_at' => now(),
                ]);

            // Broadcast call cancelled / missed event so apps stop ringing immediately
            try {
                event(new CallCancelled($session, 'timeout_missed'));
            } catch (\Throwable $e) {
                Log::warning("Call timeout broadcast warning: " . $e->getMessage());
            }

            $count++;
        }

        // 2. Process any orphaned records in calls table
        $staleCalls = Call::whereIn('status', ['calling', 'ringing'])
            ->whereNull('answered_at')
            ->where('created_at', '<=', $timeoutLimit)
            ->get();

        foreach ($staleCalls as $call) {
            $call->update([
                'status'   => 'missed',
                'ended_at' => now(),
            ]);

            if ($call->caller_id) {
                Cache::forget("user:{$call->caller_id}:is_busy");
            }
            if ($call->receiver_id) {
                Cache::forget("user:{$call->receiver_id}:is_busy");
            }

            try {
                event(new CallCancelled($call, 'timeout_missed'));
            } catch (\Throwable $e) {}

            $count++;
        }

        if ($count > 0) {
            $this->info("Cleaned up {$count} timed-out call(s).");
        }

        return Command::SUCCESS;
    }
}
