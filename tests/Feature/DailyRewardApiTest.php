<?php

namespace Tests\Feature;

use App\Models\DailyReward;
use App\Models\User;
use App\Models\UserDailyClaim;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyRewardApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'coins' => 100,
        ]);

        $this->seed(\Database\Seeders\DailyRewardSeeder::class);
    }

    public function test_get_status_returns_7_days_and_can_claim_true_for_new_user(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/daily-rewards/status');

        $response->assertStatus(200)
            ->assertJson([
                'status'            => true,
                'should_open_popup' => true,
                'can_claim'         => true,
                'current_streak'    => 0,
                'next_day_number'   => 1,
            ])
            ->assertJsonStructure([
                'status',
                'should_open_popup',
                'can_claim',
                'tomorrow_reward' => ['coins', 'text'],
                'user_current_coins',
                'days' => [
                    '*' => ['day_number', 'reward_coins', 'icon_image', 'is_claimed', 'is_current', 'is_locked']
                ]
            ]);

        $this->assertCount(7, $response->json('days'));
    }

    public function test_user_can_claim_day_1_reward_successfully(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/daily-rewards/claim');

        $response->assertStatus(200)
            ->assertJson([
                'status'        => true,
                'claimed_day'   => 1,
                'coins_awarded' => 50,
                'total_balance' => 150,
            ]);

        $this->assertEquals(150, $this->user->fresh()->coins);
        $this->assertDatabaseHas('user_daily_claims', [
            'user_id'       => $this->user->id,
            'day_claimed'   => 1,
            'coins_awarded' => 50,
        ]);
    }

    public function test_user_cannot_claim_again_within_12_hours(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        // 1st claim
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/daily-rewards/claim');

        // Immediate 2nd claim (within 12 hours)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/daily-rewards/claim');

        $response->assertStatus(422)
            ->assertJson([
                'status'  => false,
                'message' => 'Please wait 12 hours before claiming the next reward.',
            ]);

        // Status API should return can_claim = false, should_open_popup = false
        $statusResp = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/daily-rewards/status');

        $statusResp->assertStatus(200)
            ->assertJson([
                'status'            => true,
                'should_open_popup' => false,
                'can_claim'         => false,
            ]);
    }

    public function test_user_can_claim_day_2_after_12_hours(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        // Simulate 1st claim 13 hours ago
        UserDailyClaim::create([
            'user_id'       => $this->user->id,
            'day_claimed'   => 1,
            'coins_awarded' => 50,
            'claimed_at'    => Carbon::now()->subHours(13),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/daily-rewards/claim');

        $response->assertStatus(200)
            ->assertJson([
                'status'        => true,
                'claimed_day'   => 2,
                'coins_awarded' => 50,
            ]);

        $this->assertDatabaseHas('user_daily_claims', [
            'user_id'     => $this->user->id,
            'day_claimed' => 2,
        ]);
    }
}
