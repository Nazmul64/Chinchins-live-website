<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VipCard;
use App\Models\VipPrivilegeCard;
use App\Models\UserVipCardSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VipCardApiAndPurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected VipPrivilegeCard $card;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'coins' => 1000,
        ]);

        $this->card = VipPrivilegeCard::create([
            'card_type'                 => 'monthly_super',
            'name'                      => 'Super Monthly VIP Card',
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
    }

    public function test_vip_card_model_points_to_vip_privilege_cards_table(): void
    {
        $cards = VipCard::all();
        $this->assertGreaterThanOrEqual(1, $cards->count());
        $this->assertEquals('vip_privilege_cards', (new VipCard())->getTable());
    }

    public function test_vip_cards_index_returns_non_null_fields(): void
    {
        $response = $this->getJson('/api/vip-cards');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        $json = $response->json();
        $cards = $json['cards'] ?? $json['data']['cards'] ?? [];
        $this->assertNotEmpty($cards);

        $firstCard = $cards[0];
        $this->assertEquals($this->card->id, $firstCard['id']);
        $this->assertEquals('Super Monthly VIP Card', $firstCard['name']);
        $this->assertIsFloat((float) $firstCard['price']);
        $this->assertEquals(32940, $firstCard['diamonds_reward']);
        $this->assertEquals(300, $firstCard['cost_diamonds']);
        $this->assertEquals(26330, $firstCard['daily_checkin_diamonds']);
        $this->assertNotNull($firstCard['perks']);
        $this->assertNotNull($firstCard['outfits']);
    }

    public function test_vip_card_purchase_with_sanctum_token(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/vip-cards/purchase', [
                'card_id' => $this->card->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        $this->assertDatabaseHas('user_vip_card_subscriptions', [
            'user_id'     => $this->user->id,
            'vip_card_id' => $this->card->id,
            'status'      => 'active',
        ]);
    }
}
