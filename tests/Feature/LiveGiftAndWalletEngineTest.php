<?php

namespace Tests\Feature;

use App\Events\LiveGiftSentEvent;
use App\Models\CoinPackage;
use App\Models\CoinPurchaseLog;
use App\Models\Gift;
use App\Models\GiftTransaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LiveGiftAndWalletEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $sender;
    protected User $receiver;
    protected Gift $gift;
    protected CoinPackage $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sender = User::factory()->create([
            'name'  => 'Sender Fan',
            'coins' => 2000,
        ]);
        Wallet::create([
            'user_id'  => $this->sender->id,
            'balance'  => 2000,
            'earnings' => 0,
        ]);

        $this->receiver = User::factory()->create([
            'name'  => 'Host Broadcaster',
            'coins' => 100,
        ]);
        Wallet::create([
            'user_id'  => $this->receiver->id,
            'balance'  => 100,
            'earnings' => 0,
        ]);

        $this->gift = Gift::create([
            'name'           => 'Private Jet',
            'coins'          => 1200,
            'coin_price'     => 1200,
            'category'       => 'luxury',
            'image'          => 'uploads/gifts/icons/jet.png',
            'icon_url'       => 'uploads/gifts/icons/jet.png',
            'animation_url'  => 'uploads/gifts/animations/jet.svga',
            'file_url'       => 'uploads/gifts/animations/jet.svga',
            'format'         => 'svga',
            'display_type'   => 'fullscreen',
            'is_broadcast'   => true,
            'is_active'      => true,
        ]);

        $this->package = CoinPackage::create([
            'title'       => 'Starter Pack',
            'coins'       => 500,
            'bonus_coins' => 50,
            'price'       => 5.00,
            'currency'    => 'BDT',
            'is_active'   => true,
        ]);
    }

    public function test_gift_catalog_api_returns_active_gifts(): void
    {
        $response = $this->getJson('/api/gifts');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ])
            ->assertJsonFragment([
                'name'         => 'Private Jet',
                'coin_price'   => 1200,
                'format'       => 'svga',
                'display_type' => 'fullscreen',
            ]);
    }

    public function test_send_gift_deducts_sender_balance_credits_host_and_fires_reverb_event(): void
    {
        Event::fake([LiveGiftSentEvent::class]);

        $response = $this->actingAs($this->sender, 'sanctum')->postJson('/api/gifts/send', [
            'stream_id'   => 'stream_1001',
            'receiver_id' => $this->receiver->id,
            'gift_id'     => $this->gift->id,
            'quantity'    => 1,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'            => true,
                'message'           => 'Gift sent successfully!',
                'remaining_balance' => 800,
            ]);

        // Check sender wallet
        $senderWallet = Wallet::where('user_id', $this->sender->id)->first();
        $this->assertEquals(800, $senderWallet->balance);

        // Check receiver wallet earnings
        $receiverWallet = Wallet::where('user_id', $this->receiver->id)->first();
        $this->assertEquals(1200, $receiverWallet->earnings);

        // Check gift transaction logged
        $this->assertDatabaseHas('gift_transactions', [
            'stream_id'   => 'stream_1001',
            'sender_id'   => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'gift_id'     => $this->gift->id,
            'coins_spent' => 1200,
        ]);

        // Assert LiveGiftSentEvent was broadcasted
        Event::assertDispatched(LiveGiftSentEvent::class, function ($event) {
            return $event->streamId === 'stream_1001'
                && $event->giftData['gift_name'] === 'Private Jet'
                && $event->giftData['format'] === 'svga'
                && $event->giftData['display_type'] === 'fullscreen';
        });
    }

    public function test_send_gift_fails_when_insufficient_coins(): void
    {
        // Set sender balance below gift price
        $senderWallet = Wallet::where('user_id', $this->sender->id)->first();
        $senderWallet->update(['balance' => 500]);
        $this->sender->update(['coins' => 500]);

        $response = $this->actingAs($this->sender, 'sanctum')->postJson('/api/gifts/send', [
            'stream_id'   => 'stream_1001',
            'receiver_id' => $this->receiver->id,
            'gift_id'     => $this->gift->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => false,
            ]);

        // Balance should remain unchanged
        $this->assertEquals(500, $senderWallet->fresh()->balance);
        $this->assertEquals(0, Wallet::where('user_id', $this->receiver->id)->first()->earnings);
    }

    public function test_coin_package_purchase_flow_adds_coins_and_creates_log(): void
    {
        $response = $this->actingAs($this->sender, 'sanctum')->postJson('/api/coins/purchase', [
            'package_id' => $this->package->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data'   => [
                    'coins_added' => 550, // 500 base + 50 bonus
                ],
            ]);

        // Check wallet
        $senderWallet = Wallet::where('user_id', $this->sender->id)->first();
        $this->assertEquals(2550, $senderWallet->balance);

        // Check purchase log
        $this->assertDatabaseHas('coin_purchase_logs', [
            'user_id'    => $this->sender->id,
            'package_id' => $this->package->id,
            'coins'      => 550,
        ]);
    }
}
