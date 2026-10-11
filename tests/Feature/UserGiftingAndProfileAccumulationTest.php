<?php

namespace Tests\Feature;

use App\Models\Gift;
use App\Models\LiveStream;
use App\Models\PartyRoom;
use App\Models\User;
use App\Models\UserGift;
use App\Models\GiftTransaction;
use App\Models\Wallet;
use App\Models\CallSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGiftingAndProfileAccumulationTest extends TestCase
{
    use RefreshDatabase;

    protected User $sender;
    protected User $receiver;
    protected Gift $gift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sender = User::factory()->create([
            'name'           => 'Sender User',
            'coins'          => 10000,
            'received_coins' => 0,
        ]);
        Wallet::create([
            'user_id'  => $this->sender->id,
            'balance'  => 10000,
            'earnings' => 0,
        ]);

        $this->receiver = User::factory()->create([
            'name'           => 'Receiver Host',
            'coins'          => 500,
            'received_coins' => 0,
        ]);
        Wallet::create([
            'user_id'  => $this->receiver->id,
            'balance'  => 500,
            'earnings' => 0,
        ]);

        $this->gift = Gift::create([
            'name'           => 'Golden Dragon',
            'coins'          => 1000,
            'coin_price'     => 1000,
            'category'       => 'luxury',
            'image'          => 'uploads/gifts/dragon.png',
            'icon_url'       => 'uploads/gifts/dragon.png',
            'animation_url'  => 'uploads/gifts/dragon.svga',
            'is_active'      => true,
        ]);
    }

    public function test_send_gift_accumulates_in_receiver_profile_and_history(): void
    {
        $response = $this->actingAs($this->sender, 'sanctum')->postJson('/api/gifts/send', [
            'receiver_id' => $this->receiver->id,
            'gift_id'     => $this->gift->id,
            'quantity'    => 2,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        // 1. Verify DB records
        $this->assertDatabaseHas('user_gifts', [
            'user_id'     => $this->receiver->id,
            'receiver_id' => $this->receiver->id,
            'sender_id'   => $this->sender->id,
            'gift_id'     => $this->gift->id,
            'quantity'    => 2,
            'total_coins' => 2000,
        ]);

        $this->assertDatabaseHas('gift_transactions', [
            'receiver_id' => $this->receiver->id,
            'sender_id'   => $this->sender->id,
            'gift_id'     => $this->gift->id,
            'quantity'    => 2,
            'total_coins' => 2000,
        ]);

        // 2. Receiver received_coins updated
        $this->receiver->refresh();
        $this->assertEquals(2000, $this->receiver->received_coins);

        // 3. GET /api/v1/user/received-gifts?user_id=RECEIVER returns the gift
        $receivedRes = $this->actingAs($this->sender, 'sanctum')->getJson('/api/v1/user/received-gifts?user_id=' . $this->receiver->id);
        $receivedRes->assertStatus(200)
            ->assertJson([
                'status' => true,
            ])
            ->assertJsonPath('data.summary.total_coins', 2000)
            ->assertJsonPath('data.top_fan.id', $this->sender->id);

        // 4. GET /api/gifts/received/RECEIVER returns the gift
        $giftsRes = $this->getJson('/api/gifts/received/' . $this->receiver->id);
        $giftsRes->assertStatus(200)
            ->assertJson([
                'status' => true,
            ])
            ->assertJsonPath('data.summary.total_coins', 2000);

        // 5. GET /api/profile/RECEIVER returns the gift and charm level
        $profileRes = $this->getJson('/api/profile/' . $this->receiver->id);
        $profileRes->assertStatus(200)
            ->assertJson([
                'status' => true,
            ])
            ->assertJsonPath('data.charm_level.level', 1)
            ->assertJsonPath('data.summary.total_coins', 2000);
    }

    public function test_connected_call_ignores_late_cancel_or_timeout_requests(): void
    {
        $call = CallSession::create([
            'caller_id'    => $this->sender->id,
            'receiver_id'  => $this->receiver->id,
            'channel_name' => 'test_call_channel_123',
            'status'       => 'connected',
            'started_at'   => now(),
            'answered_at'  => now(),
        ]);

        $cancelRes = $this->actingAs($this->sender, 'sanctum')->postJson('/api/call/cancel', [
            'call_id'      => $call->id,
            'channel_name' => $call->channel_name,
            'reason'       => 'timeout_missed',
        ]);

        $cancelRes->assertStatus(200);

        // Call status remains connected, not ended or missed
        $call->refresh();
        $this->assertEquals('connected', $call->status);
    }

    public function test_live_stream_send_gift_accumulates_in_host_profile(): void
    {
        $stream = LiveStream::create([
            'host_id'      => $this->receiver->id,
            'channel_name' => 'live_channel_host_99',
            'status'       => 'live',
        ]);

        $response = $this->actingAs($this->sender, 'sanctum')->postJson('/api/v1/stream/send-gift', [
            'room_id'  => (string) $stream->id,
            'gift_id'  => $this->gift->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        // Host receives gift in UserGift
        $this->assertDatabaseHas('user_gifts', [
            'user_id'     => $this->receiver->id,
            'receiver_id' => $this->receiver->id,
            'sender_id'   => $this->sender->id,
            'gift_id'     => $this->gift->id,
            'quantity'    => 1,
            'total_coins' => 1000,
        ]);

        // Host profile reflects received gift
        $profileRes = $this->getJson('/api/v1/user/received-gifts?user_id=' . $this->receiver->id);
        $profileRes->assertStatus(200)
            ->assertJsonPath('data.summary.total_coins', 1000);
    }

    public function test_party_room_send_gift_accumulates_in_receiver_profile(): void
    {
        $room = PartyRoom::create([
            'host_id'      => $this->receiver->id,
            'room_id'      => 'party_room_voice_101',
            'title'        => 'Music & Chill Voice Room',
            'channel_name' => 'party_room_voice_101',
            'status'       => 'active',
        ]);

        $response = $this->actingAs($this->sender, 'sanctum')->postJson('/api/party-rooms/' . $room->id . '/send-gift', [
            'receiver_id' => $this->receiver->id,
            'gift_id'     => $this->gift->id,
            'count'       => 1,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Receiver profile reflects gift
        $this->assertDatabaseHas('user_gifts', [
            'user_id'     => $this->receiver->id,
            'receiver_id' => $this->receiver->id,
            'sender_id'   => $this->sender->id,
            'gift_id'     => $this->gift->id,
            'context'     => 'party_room',
        ]);

        $profileRes = $this->getJson('/api/profile/' . $this->receiver->id);
        $profileRes->assertStatus(200)
            ->assertJsonPath('data.summary.total_coins', 1000);
    }
}
