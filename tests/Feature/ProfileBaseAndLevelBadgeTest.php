<?php

namespace Tests\Feature;

use App\Models\ProfileBase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileBaseAndLevelBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_bases_api_returns_levels_and_frames()
    {
        $response = $this->getJson('/api/profile-bases');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'total',
                'data' => [
                    '*' => [
                        'id',
                        'level',
                        'name',
                        'required_coins',
                        'frame_image_url',
                        'badge_icon',
                        'badge_color',
                        'glow_color',
                        'privilege_text',
                        'is_active',
                    ]
                ]
            ]);

        $this->assertGreaterThanOrEqual(10, $response->json('total'));
    }

    public function test_user_level_status_progression_calculation()
    {
        $user = User::factory()->create([
            'coins' => 50000,
            'level' => 1,
        ]);

        $response = $this->getJson('/api/user/level-status?user_id=' . $user->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'account_id',
                        'total_earned_coins',
                    ],
                    'progression' => [
                        'current_level',
                        'level_name',
                        'avatar_frame_url',
                        'badge_color',
                        'badge_icon',
                        'progress_percentage',
                    ],
                    'levels_scale',
                ]
            ]);
    }

    public function test_user_model_appends_avatar_frame_url()
    {
        $user = User::factory()->create();

        $this->assertArrayHasKey('avatar_frame_url', $user->toArray());
        $this->assertArrayHasKey('current_level', $user->toArray());
        $this->assertArrayHasKey('total_earned_coins', $user->toArray());
        $this->assertArrayHasKey('level_info', $user->toArray());
    }
}
