<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_via_api(): void
    {
        // Delete test user if exists
        User::where('email', 'flutter_test@chinchins.live')->orWhere('phone', '01711223344')->delete();

        $response = $this->postJson('/api/register', [
            'first_name'            => 'Flutter',
            'last_name'             => 'User',
            'phone'                 => '01711223344',
            'email'                 => 'flutter_test@chinchins.live',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => ['id', 'first_name', 'last_name', 'phone', 'email'],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'flutter_test@chinchins.live',
            'phone' => '01711223344',
            'first_name' => 'Flutter',
            'last_name' => 'User',
        ]);
    }

    public function test_user_can_login_with_email(): void
    {
        User::create([
            'first_name' => 'Flutter',
            'last_name'  => 'User',
            'name'       => 'Flutter User',
            'phone'      => '01711223344',
            'email'      => 'flutter_test@chinchins.live',
            'password'   => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'identifier' => 'flutter_test@chinchins.live',
            'password'   => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['user', 'token'],
            ]);
    }

    public function test_user_can_login_with_phone(): void
    {
        User::create([
            'first_name' => 'Flutter',
            'last_name'  => 'User',
            'name'       => 'Flutter User',
            'phone'      => '01711223344',
            'email'      => 'flutter_test@chinchins.live',
            'password'   => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'identifier' => '01711223344',
            'password'   => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['user', 'token'],
            ]);
    }
}
