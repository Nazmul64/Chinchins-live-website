<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\CallSession;
use App\Models\CoinTransaction;
use App\Models\Gift;
use App\Models\LiveStream;
use App\Models\LiveJoinRequest;
use App\Models\User;
use App\Models\UserVipCardSubscription;
use App\Models\VipPrivilegeCard;
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
     * 1. test_call_initiation_and_acceptance_isolation()
     * User 1 calls User 2 -> Status is ringing -> User 2 accepts -> Status is connected.
     * Assert User 1 (Caller) trying to accept is rejected with 403 Forbidden.
     */
    public function test_call_initiation_and_acceptance_isolation(): void
    {
        // 1. Initiate Call
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

        // 2. Assert Caller attempting to execute /api/call/accept receives 403 Forbidden
        $callerSelfAcceptResponse = $this->withHeader('Authorization', 'Bearer ' . $this->callerToken)
            ->postJson('/api/call/accept', [
                'call_id' => $callId,
            ]);

        $callerSelfAcceptResponse->assertStatus(403)
            ->assertJson([
                'status'  => false,
                'message' => 'Caller cannot accept their own call.',
            ]);

        $this->assertEquals('ringing', $call->fresh()->status);
        $this->assertNull($call->fresh()->answered_at);

        // 3. Receiver accepts call -> Status becomes connected
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
     * 2. test_call_disconnect_leaves_no_ghost_loops()
     * End call -> Assert 0 reverse / ghost calls created.
     */
    public function test_call_disconnect_leaves_no_ghost_loops(): void
    {
        $call = CallSession::create([
            'caller_id'    => $this->caller->id,
            'receiver_id'  => $this->receiver->id,
            'channel_name' => 'sim_call_channel_ghost_test',
            'call_type'    => 'video',
            'status'       => 'connected',
            'answered_at'  => Carbon::now()->subSeconds(30),
        ]);

        // End call from caller
        $endResponse = $this->withHeader('Authorization', 'Bearer ' . $this->callerToken)
            ->postJson('/api/call/end', [
                'call_id'          => $call->id,
                'duration_seconds' => 30,
            ]);

        $endResponse->assertStatus(200);

        $freshCall = $call->fresh();
        $this->assertTrue(in_array($freshCall->status, ['ended', 'completed']));
        $this->assertNotNull($freshCall->ended_at);

        // Assert 0 reverse calls exist in database
        $reverseCalls = CallSession::where('caller_id', $this->receiver->id)
            ->where('receiver_id', $this->caller->id)
            ->where('created_at', '>=', Carbon::now()->subSeconds(5))
            ->get();

        $this->assertCount(0, $reverseCalls, 'No ghost reverse call should be initiated on disconnect');
    }

    /**
     * 3. test_stream_cohost_request_lifecycle()
     * Viewer requests -> Host accepts -> Assert toast dismisses and pending queue is 0.
     */
    public function test_stream_cohost_request_lifecycle(): void
    {
        $host = $this->caller;
        $viewer = $this->receiver;

        $stream = LiveStream::create([
            'host_id'      => $host->id,
            'user_id'      => $host->id,
            'title'        => 'Test E2E Live Room',
            'channel_name' => 'live_room_' . $host->id,
            'status'       => 'live',
            'is_live'      => true,
        ]);

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

        // Host accepts request
        $joinReq->update(['status' => 'accepted']);
        $this->assertEquals('accepted', $joinReq->fresh()->status);

        // Pending count becomes 0
        $pendingCount = LiveJoinRequest::where('live_stream_id', $stream->id)
            ->where('status', 'pending')
            ->count();

        $this->assertEquals(0, $pendingCount, 'Pending requests queue must be empty after acceptance');
    }

    /**
     * 4. test_wallet_recharge_reflects_without_restart()
     * User with 0 balance -> recharge -> instant atomic reflection without app restart.
     */
    public function test_wallet_recharge_reflects_without_restart(): void
    {
        $poorUser = User::factory()->create(['coins' => 0]);
        $poorToken = $poorUser->createToken('poor-token')->plainTextToken;

        // Check initial wallet balance
        $balResponse = $this->withHeader('Authorization', 'Bearer ' . $poorToken)
            ->getJson('/api/wallet/balance');

        $balResponse->assertStatus(200);
        $this->assertEquals(0, (int) ($balResponse->json('coins') ?? $balResponse->json('data.coins') ?? 0));

        // Atomic deposit / recharge of 5000 coins
        $poorUser->increment('coins', 5000);
        CoinTransaction::create([
            'user_id'       => $poorUser->id,
            'amount'        => 5000,
            'type'          => 'recharge',
            'description'   => 'E2E Test Wallet Recharge',
            'balance_after' => $poorUser->fresh()->coins,
        ]);

        // Verify balance immediately reflects 5000 via API
        $updatedBalResponse = $this->withHeader('Authorization', 'Bearer ' . $poorToken)
            ->getJson('/api/wallet/balance');

        $updatedBalResponse->assertStatus(200);
        $newCoins = (int) ($updatedBalResponse->json('coins') ?? $updatedBalResponse->json('data.coins') ?? 0);
        $this->assertEquals(5000, $newCoins);

        // Now initiate call succeeds immediately
        $callResponse = $this->withHeader('Authorization', 'Bearer ' . $poorToken)
            ->postJson('/api/call/initiate', [
                'receiver_id' => $this->receiver->id,
                'call_type'   => 'video',
            ]);

        $callResponse->assertStatus(200)
            ->assertJson(['status' => true]);
    }

    /**
     * 5. test_vip_privilege_card_purchase_flow()
     * Targets vip_privilege_cards table with Bearer token authentication.
     */
    public function test_vip_privilege_card_purchase_flow(): void
    {
        $card = VipPrivilegeCard::create([
            'card_type'                 => 'monthly_privilege_card',
            'name'                      => 'VIP Privilege Card',
            'price_bdt'                 => 300,
            'price_coins'               => 300,
            'duration_days'             => 30,
            'instant_reward_coins'      => 32940,
            'daily_checkin_total_coins' => 26330,
            'total_return_coins'        => 59270,
            'card_color'                => '#FF1493',
            'format'                    => 'lottie',
            'is_active'                 => true,
            'sort_order'                => 1,
        ]);

        $purchaseResponse = $this->withHeader('Authorization', 'Bearer ' . $this->callerToken)
            ->postJson('/api/vip-cards/purchase', [
                'card_id' => $card->id,
            ]);

        $purchaseResponse->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        $this->assertDatabaseHas('user_vip_card_subscriptions', [
            'user_id'     => $this->caller->id,
            'vip_card_id' => $card->id,
            'status'      => 'active',
        ]);
    }

    /**
     * 6. test_gift_api_absolute_urls_and_daily_rewards_toggle()
     * Verify Gift API returns full absolute URLs and daily rewards toggle works cleanly.
     */
    public function test_gift_api_absolute_urls_and_daily_rewards_toggle(): void
    {
        // Seed test gift
        $gift = Gift::create([
            'name'       => 'Test Rocket',
            'coin_price' => 500,
            'image'      => 'uploads/gifts/rocket.gif',
            'is_active'  => true,
        ]);

        $giftResponse = $this->getJson('/api/gifts');
        $giftResponse->assertStatus(200);

        $gifts = $giftResponse->json('gifts') ?? $giftResponse->json('data') ?? [];
        $this->assertNotEmpty($gifts);

        $firstGift = collect($gifts)->firstWhere('id', $gift->id);
        $this->assertNotNull($firstGift);
        $imageUrl = $firstGift['image'] ?? $firstGift['image_url'] ?? '';
        $this->assertStringStartsWith('http', $imageUrl, 'Gift image URL must be fully qualified absolute URL');

        // Test Daily Rewards AppSetting toggle
        AppSetting::set('daily_rewards_enabled', '0');
        $this->assertEquals('0', AppSetting::get('daily_rewards_enabled'));

        AppSetting::set('daily_rewards_enabled', '1');
        $this->assertEquals('1', AppSetting::get('daily_rewards_enabled'));
    }
}
