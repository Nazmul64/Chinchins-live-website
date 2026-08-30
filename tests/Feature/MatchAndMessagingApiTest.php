<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\CoinPackage;
use App\Models\PaymentMethod;
use App\Models\ProfileView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchAndMessagingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_match_tab_returns_available_hosts_and_live_waiting_count(): void
    {
        $femaleHost = User::factory()->create([
            'name' => 'Sara Khan',
            'gender' => 'female',
            'is_active' => true,
            'is_busy' => false,
            'video_call_rate' => 150,
        ]);

        $caller = User::factory()->create([
            'name' => 'John Doe',
            'coins' => 500,
        ]);

        $response = $this->getJson('/api/match?user_id=' . $caller->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Match tab data loaded successfully.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'waiting_count',
                    'actual_online_hosts',
                    'heading',
                    'button_text',
                    'hosts',
                    'caller',
                ],
            ]);
    }

    public function test_start_matching_succeeds_when_caller_has_enough_coins(): void
    {
        $host = User::factory()->create([
            'name' => 'Ameena Host',
            'gender' => 'female',
            'is_active' => true,
            'is_busy' => false,
            'video_call_rate' => 100,
        ]);

        $caller = User::factory()->create([
            'name' => 'Rich Caller',
            'coins' => 1000,
            'free_calls_used' => 1,
        ]);

        $response = $this->postJson('/api/match/start', [
            'user_id' => $caller->id,
            'call_type' => 'video',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'matched_host',
                    'call_session' => [
                        'call_id',
                        'channel_name',
                        'status',
                    ],
                ],
            ]);
    }

    public function test_start_matching_returns_low_balance_deposit_required_when_insufficient_coins(): void
    {
        CoinPackage::create([
            'coins' => 1000,
            'price' => 100,
            'is_active' => true,
        ]);

        $host = User::factory()->create([
            'name' => 'Premium Host',
            'gender' => 'female',
            'is_active' => true,
            'is_busy' => false,
            'video_call_rate' => 1800,
        ]);

        $caller = User::factory()->create([
            'name' => 'Broke Caller',
            'coins' => 10,
            'free_calls_used' => 1,
        ]);

        $response = $this->postJson('/api/match/start', [
            'user_id' => $caller->id,
            'call_type' => 'video',
        ]);

        $response->assertStatus(402)
            ->assertJson([
                'status' => false,
                'code' => 'LOW_BALANCE_DEPOSIT_REQUIRED',
                'is_low_balance' => true,
                'redirect_to_deposit' => true,
            ])
            ->assertJsonStructure([
                'required_coins',
                'current_coins',
                'coin_packages',
            ]);
    }

    public function test_record_profile_view_triggers_auto_callback(): void
    {
        $host = User::factory()->create([
            'name' => 'Sohan Khan',
            'gender' => 'female',
            'video_call_rate' => 1800,
        ]);

        $viewer = User::factory()->create([
            'name' => 'Viewer User',
            'coins' => 2000,
        ]);

        $response = $this->postJson("/api/profile/{$host->id}/view", [
            'user_id' => $viewer->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Profile view recorded. Auto-callback notification triggered.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'host',
                    'callback' => [
                        'auto_call_triggered',
                    ],
                    'auto_message',
                ],
            ]);

        $this->assertDatabaseHas('profile_views', [
            'viewer_id' => $viewer->id,
            'host_id' => $host->id,
        ]);
    }

    public function test_messaging_and_free_limit_enforcement(): void
    {
        $host = User::factory()->create(['name' => 'Gulabi']);
        $user = User::factory()->create([
            'name' => 'Chatter',
            'coins' => 0,
            'free_messages_used' => 5,
            'free_messages_limit' => 5,
        ]);

        // Attempt sending message when free limit is exhausted and 0 coins
        $response = $this->postJson('/api/messages/send', [
            'user_id' => $user->id,
            'receiver_id' => $host->id,
            'type' => 'text',
            'message' => 'Hello there!',
        ]);

        $response->assertStatus(402)
            ->assertJson([
                'status' => false,
                'code' => 'MESSAGE_LIMIT_REACHED',
                'is_limit_reached' => true,
                'redirect_to_deposit' => true,
            ]);

        // When user has coins
        $user->update(['coins' => 50]);

        $successResponse = $this->postJson('/api/messages/send', [
            'user_id' => $user->id,
            'receiver_id' => $host->id,
            'type' => 'text',
            'message' => 'Hello with paid coin!',
        ]);

        $successResponse->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Message sent successfully.',
            ]);

        $this->assertEquals(45, $user->fresh()->coins);
    }
}
