<?php

namespace Tests\Feature;

use App\Models\CallSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallAcceptValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $caller;
    protected User $receiver;
    protected User $stranger;
    protected CallSession $call;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caller = User::factory()->create();
        $this->receiver = User::factory()->create();
        $this->stranger = User::factory()->create();

        $this->call = CallSession::create([
            'caller_id'    => $this->caller->id,
            'receiver_id'  => $this->receiver->id,
            'channel_name' => 'call_test_channel_123',
            'call_type'    => 'video',
            'status'       => 'ringing',
        ]);
    }

    public function test_caller_cannot_accept_their_own_call(): void
    {
        $token = $this->caller->createToken('caller-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/call/accept', [
                'call_id' => $this->call->id,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status'  => false,
                'message' => 'Caller cannot accept their own call.',
            ]);
    }

    public function test_stranger_cannot_accept_call(): void
    {
        $token = $this->stranger->createToken('stranger-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/call/accept', [
                'call_id' => $this->call->id,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status'  => false,
                'message' => 'Unauthorized: You are not the receiver of this call.',
            ]);
    }

    public function test_receiver_can_accept_call(): void
    {
        $token = $this->receiver->createToken('receiver-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/call/accept', [
                'call_id' => $this->call->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        $this->assertEquals('connected', $this->call->fresh()->status);
    }
}
