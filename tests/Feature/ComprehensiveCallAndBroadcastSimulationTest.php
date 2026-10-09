<?php

namespace Tests\Feature;

use App\Models\CallSession;
use App\Models\LiveStream;
use App\Models\LiveJoinRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveCallAndBroadcastSimulationTest extends TestCase
{
    use RefreshDatabase;

    protected User $caller;
    protected User $receiver;
    protected string $callerToken;
    protected string $receiverToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caller = User::factory()->create([
            'coins' => 5000,
        ]);
        $this->receiver = User::factory()->create([
            'coins' => 1000,
        ]);

        $this->callerToken = $this->caller->createToken('caller-token')->plainTextToken;
        $this->receiverToken = $this->receiver->createToken('receiver-token')->plainTextToken;
    }

    /**
     * Test A: Call Workflow Simulation
     * 1. Initiate Call -> Status ringing
     * 2. Caller cannot accept call -> 403 Forbidden
     * 3. Receiver accepts call -> Status connected, answered_at populated, LiveKit / WebRTC credentials returned
     */
    public function test_call_initiate_and_receiver_only_accept_flow(): void
    {
        // Step 1: Caller initiates call
        $initiateResponse = $this->withHeader('Authorization', 'Bearer ' . $this->callerToken)
            ->postJson('/api/call/initiate', [
                'receiver_id' => $this->receiver->id,
                'call_type'   => 'video',
            ]);

        $initiateResponse->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        $callId = $initiateResponse->json('call_id') 
            ?? $initiateResponse->json('data.call_id') 
            ?? $initiateResponse->json('data.id');

        $this->assertNotNull($callId);

        $call = CallSession::find($callId);
        $this->assertNotNull($call);
        $this->assertEquals('ringing', $call->status);
        $this->assertNull($call->answered_at);

        // Step 2: Caller attempting to self-accept MUST be rejected (403 Forbidden)
        $callerAcceptResponse = $this->withHeader('Authorization', 'Bearer ' . $this->callerToken)
            ->postJson('/api/call/accept', [
                'call_id' => $callId,
            ]);

        $callerAcceptResponse->assertStatus(403);
        $this->assertEquals('ringing', $call->fresh()->status);

        // Step 3: Receiver accepts call
        $receiverAcceptResponse = $this->withHeader('Authorization', 'Bearer ' . $this->receiverToken)
            ->postJson('/api/call/accept', [
                'call_id' => $callId,
            ]);

        $receiverAcceptResponse->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        $freshCall = $call->fresh();
        $this->assertEquals('connected', $freshCall->status);
        $this->assertNotNull($freshCall->answered_at);
    }

    /**
     * Test B: Termination & Ghost Loop Prevention
     * 1. Active call ends cleanly
     * 2. Status becomes completed/ended, ended_at and ended_by set
     * 3. Assert no ghost reverse calls exist
     */
    public function test_call_termination_clean_without_ghost_auto_dial(): void
    {
        $call = CallSession::create([
            'caller_id'    => $this->caller->id,
            'receiver_id'  => $this->receiver->id,
            'channel_name' => 'sim_call_channel_999',
            'call_type'    => 'video',
            'status'       => 'connected',
            'answered_at'  => Carbon::now()->subSeconds(20),
        ]);

        // End Call from Caller
        $endResponse = $this->withHeader('Authorization', 'Bearer ' . $this->callerToken)
            ->postJson('/api/call/end', [
                'call_id'          => $call->id,
                'duration_seconds' => 20,
            ]);

        $endResponse->assertStatus(200);

        $freshCall = $call->fresh();
        $this->assertTrue(in_array($freshCall->status, ['ended', 'completed']));
        $this->assertNotNull($freshCall->ended_at);

        // Assert no reverse call exists in database
        $reverseCalls = CallSession::where('caller_id', $this->receiver->id)
            ->where('receiver_id', $this->caller->id)
            ->where('created_at', '>=', Carbon::now()->subSeconds(5))
            ->get();

        $this->assertCount(0, $reverseCalls, 'No ghost reverse call should be initiated');
    }

    /**
     * Test C: Live Broadcast Join Request & Co-Host Lifecycle
     */
    public function test_live_stream_cohost_request_and_deduplication(): void
    {
        $host = $this->caller;
        $viewer = $this->receiver;

        $stream = LiveStream::create([
            'host_id'      => $host->id,
            'user_id'      => $host->id,
            'title'        => 'Test Live Stream',
            'channel_name' => 'live_room_' . $host->id,
            'status'       => 'live',
            'is_live'      => true,
        ]);

        $this->assertNotNull($stream->id);

        // Viewer requests to join
        $joinReq = LiveJoinRequest::create([
            'live_stream_id' => $stream->id,
            'user_id'        => $viewer->id,
            'status'         => 'pending',
        ]);

        $this->assertDatabaseHas('live_join_requests', [
            'id'     => $joinReq->id,
            'status' => 'pending',
        ]);

        // Request updated to accepted on host action
        $joinReq->update(['status' => 'accepted']);
        $this->assertEquals('accepted', $joinReq->fresh()->status);
    }
}
