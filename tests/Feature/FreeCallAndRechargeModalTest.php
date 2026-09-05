<?php

namespace Tests\Feature;

use App\Models\CallSession;
use App\Models\CallSetting;
use App\Models\CoinPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreeCallAndRechargeModalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed Coin Packages
        $this->seed(\Database\Seeders\CoinPackageSeeder::class);
    }

    public function test_call_config_returns_16s_and_teaser_and_quick_messages()
    {
        $response = $this->getJson('/api/call/config');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'free_call_duration_seconds' => 16,
                    'video_call_rate_per_minute' => 100,
                    'host_earning_percent' => 50,
                    'free_message_chances' => 2,
                    'call_recharge_teaser_text' => "Let's play baby! Recharge and call me,I want to show you 💋",
                ],
            ]);
    }

    public function test_free_host_can_initiate_call_with_zero_coins()
    {
        $freeHost = User::factory()->create([
            'coins' => 0,
            'is_free_caller' => true,
            'gender' => 'female',
        ]);

        $receiver = User::factory()->create([
            'coins' => 0,
            'gender' => 'male',
        ]);

        $response = $this->postJson('/api/call/initiate', [
            'caller_id' => $freeHost->id,
            'receiver_id' => $receiver->id,
            'call_type' => 'video',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'is_free_trial' => true,
                    'is_caller_free' => true,
                    'free_duration_seconds' => 16,
                ],
            ]);

        $this->assertDatabaseHas('call_sessions', [
            'caller_id' => $freeHost->id,
            'receiver_id' => $receiver->id,
            'is_caller_free' => true,
            'status' => 'ringing',
        ]);
    }

    public function test_in_call_deduction_during_free_16s_charges_0_coins()
    {
        $freeHost = User::factory()->create(['coins' => 0, 'is_free_caller' => true]);
        $receiver = User::factory()->create(['coins' => 0]);

        $call = CallSession::create([
            'caller_id' => $freeHost->id,
            'receiver_id' => $receiver->id,
            'channel_name' => 'test_chan_1',
            'call_type' => 'video',
            'status' => 'connected',
            'rate_per_minute' => 100,
            'is_free_trial' => true,
            'is_caller_free' => true,
            'free_duration_seconds' => 16,
            'started_at' => now(),
        ]);

        // At 10s (within 16s)
        $response = $this->postJson('/api/call/deduct-interval', [
            'call_id' => $call->id,
            'elapsed_seconds' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'is_free_trial' => true,
                'free_seconds_remaining' => 6,
                'data' => [
                    'coins_deducted' => 0,
                    'can_continue' => true,
                ],
            ]);
    }

    public function test_in_call_deduction_after_16s_with_0_balance_prompts_recharge_sheet()
    {
        $freeHost = User::factory()->create(['coins' => 0, 'is_free_caller' => true]);
        $receiver = User::factory()->create(['coins' => 0]); // Zero balance

        $call = CallSession::create([
            'caller_id' => $freeHost->id,
            'receiver_id' => $receiver->id,
            'channel_name' => 'test_chan_2',
            'call_type' => 'video',
            'status' => 'connected',
            'rate_per_minute' => 100,
            'is_free_trial' => true,
            'is_caller_free' => true,
            'free_duration_seconds' => 16,
            'started_at' => now()->subSeconds(20),
        ]);

        // At 20s (after 16s)
        $response = $this->postJson('/api/call/deduct-interval', [
            'call_id' => $call->id,
            'elapsed_seconds' => 20,
            'coins' => 100,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => false,
                'code' => 'LOW_BALANCE_DEPOSIT_REQUIRED',
                'should_blur_video' => true,
                'show_recharge_sheet' => true,
            ]);
    }

    public function test_in_call_deduction_after_16s_with_coins_splits_50_50()
    {
        $freeHost = User::factory()->create(['coins' => 0, 'is_free_caller' => true]);
        $receiver = User::factory()->create(['coins' => 1000]); // Paying user

        $call = CallSession::create([
            'caller_id' => $freeHost->id,
            'receiver_id' => $receiver->id,
            'channel_name' => 'test_chan_3',
            'call_type' => 'video',
            'status' => 'connected',
            'rate_per_minute' => 100,
            'is_free_trial' => true,
            'is_caller_free' => true,
            'free_duration_seconds' => 16,
            'started_at' => now()->subSeconds(25),
        ]);

        // Deduct 100 coins for 1 minute
        $response = $this->postJson('/api/call/deduct-interval', [
            'call_id' => $call->id,
            'elapsed_seconds' => 25,
            'coins' => 100,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'coins_deducted' => 100,
                    'host_earned_coins' => 50,
                    'admin_revenue_coins' => 50,
                ],
            ]);

        // Receiver paid 100 coins: 1000 - 100 = 900
        $this->assertEquals(900, $receiver->fresh()->coins);

        // Female host received 50 coins (50%): 0 + 50 = 50
        $this->assertEquals(50, $freeHost->fresh()->coins);
    }

    public function test_get_recharge_sheet_modal_data()
    {
        $user = User::factory()->create(['coins' => 0]);
        $host = User::factory()->create(['nickname' => 'Angelina']);

        $response = $this->getJson("/api/call/recharge-sheet?user_id={$user->id}&host_id={$host->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'teaser_text' => "Let's play baby! Recharge and call me,I want to show you 💋",
                    'user_gems' => 0,
                    'formatted_user_gems' => 'My Gems: 0',
                ],
            ]);

        $this->assertCount(6, $response->json('data.packages'));
    }

    public function test_quick_messages_and_free_chances()
    {
        $user = User::factory()->create(['coins' => 50]);
        $host = User::factory()->create();

        // 1. Get quick messages
        $response = $this->getJson("/api/call/quick-messages?user_id={$user->id}");
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'free_chances_total' => 2,
                    'free_chances_remaining' => 2,
                ],
            ]);

        // 2. Send 1st free message
        $send1 = $this->postJson('/api/call/send-quick-message', [
            'user_id' => $user->id,
            'receiver_id' => $host->id,
            'message' => 'Be my girlfriend',
        ]);
        $send1->assertStatus(200)->assertJson(['data' => ['is_free' => true, 'free_chances_remaining' => 1]]);

        // 3. Send 2nd free message
        $send2 = $this->postJson('/api/call/send-quick-message', [
            'user_id' => $user->id,
            'receiver_id' => $host->id,
            'message' => "Hi , what's up babe?",
        ]);
        $send2->assertStatus(200)->assertJson(['data' => ['is_free' => true, 'free_chances_remaining' => 0]]);

        // Coins should still be 50 because both were free
        $this->assertEquals(50, $user->fresh()->coins);
    }

    public function test_admin_can_toggle_free_caller_status()
    {
        $admin = User::factory()->create();
        $targetUser = User::factory()->create(['is_free_caller' => false]);

        $response = $this->actingAs($admin)->post("/admin/users/{$targetUser->id}/toggle-free-caller");

        $response->assertRedirect();
        $this->assertTrue($targetUser->fresh()->is_free_caller);

        // Toggle back
        $this->actingAs($admin)->post("/admin/users/{$targetUser->id}/toggle-free-caller");
        $this->assertFalse($targetUser->fresh()->is_free_caller);
    }
}
