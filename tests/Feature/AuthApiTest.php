<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_via_api_and_gets_auto_generated_account_id(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name'            => 'Nazmul',
            'last_name'             => 'Hossain',
            'phone'                 => '01706640864',
            'email'                 => 'nazmul@gmail.com',
            'password'              => 'nazmul@gmail.com',
            'password_confirmation' => 'nazmul@gmail.com',
            'gender'                => 'female',
            'age'                   => 27,
            'country'               => 'Pakistan',
            'introduction'          => 'Sweet girl looking for honest talk ❤️',
            'languages'             => ['English', 'Urdu'],
            'tags'                  => ['Live video', 'Music'],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'account_id',
                        'first_name',
                        'last_name',
                        'name',
                        'nickname',
                        'phone',
                        'email',
                        'gender',
                        'age',
                        'country',
                        'introduction',
                        'languages',
                        'tags',
                        'is_active',
                        'level',
                        'video_call_rate',
                        'avatar_url',
                        'cover_photo_url',
                        'gallery_image_urls',
                    ],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email'        => 'nazmul@gmail.com',
            'phone'        => '01706640864',
            'first_name'   => 'Nazmul',
            'last_name'    => 'Hossain',
            'gender'       => 'female',
            'age'          => 27,
            'country'      => 'Pakistan',
            'introduction' => 'Sweet girl looking for honest talk ❤️',
        ]);

        $user = User::where('email', 'nazmul@gmail.com')->first();
        $this->assertNotNull($user->account_id);
        $this->assertGreaterThanOrEqual(9, strlen($user->account_id));
    }

    public function test_user_can_login_with_email_phone_or_account_id(): void
    {
        $user = User::create([
            'first_name' => 'Ayeena',
            'last_name'  => 'Live',
            'phone'      => '01711223344',
            'email'      => 'ayeena@chinchins.live',
            'password'   => bcrypt('password123'),
            'account_id' => '6022816358',
        ]);

        // Login with email
        $resEmail = $this->postJson('/api/login', [
            'identifier' => 'ayeena@chinchins.live',
            'password'   => 'password123',
        ]);
        $resEmail->assertStatus(200)->assertJsonPath('status', true);

        // Login with phone
        $resPhone = $this->postJson('/api/login', [
            'identifier' => '01711223344',
            'password'   => 'password123',
        ]);
        $resPhone->assertStatus(200)->assertJsonPath('status', true);

        // Login with Account ID (e.g. 6022816358)
        $resAccountId = $this->postJson('/api/login', [
            'account_id' => '6022816358',
            'password'   => 'password123',
        ]);
        $resAccountId->assertStatus(200)->assertJsonPath('status', true);
    }

    public function test_user_can_update_profile_and_status(): void
    {
        $user = User::create([
            'first_name' => 'Nazmul',
            'last_name'  => 'Hossain',
            'phone'      => '01706640864',
            'email'      => 'nazmul@gmail.com',
            'password'   => bcrypt('secret123'),
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/profile/update', [
                'nickname'     => 'Ayeena04',
                'age'          => 28,
                'gender'       => 'female',
                'country'      => 'Pakistan',
                'introduction' => 'Looking for honest friends!',
                'languages'    => ['English', 'Bengali', 'Urdu'],
                'tags'         => ['Music', 'Live video', 'Singing'],
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.user.nickname', 'Ayeena04')
            ->assertJsonPath('data.user.age', 28)
            ->assertJsonPath('data.user.country', 'Pakistan');

        $this->assertDatabaseHas('users', [
            'id'       => $user->id,
            'nickname' => 'Ayeena04',
            'age'      => 28,
        ]);

        // Toggle Status
        $statusRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/profile/status', [
                'is_active' => false,
            ]);
        $statusRes->assertStatus(200)->assertJsonPath('data.is_active', false);
    }

    public function test_user_can_upload_gallery_photos_and_avatar(): void
    {
        Storage::fake('public');

        $user = User::create([
            'first_name' => 'Nazmul',
            'last_name'  => 'Hossain',
            'phone'      => '01706640864',
            'email'      => 'nazmul@gmail.com',
            'password'   => bcrypt('secret123'),
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $photo1 = UploadedFile::fake()->image('photo1.jpg', 600, 600);
        $photo2 = UploadedFile::fake()->image('photo2.jpg', 600, 600);

        // Multi upload
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/profile/upload-photos', [
                'photos' => [$photo1, $photo2],
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true);

        $user->refresh();
        $this->assertCount(2, $user->gallery_images);
        // First image should be set as cover photo automatically
        $this->assertEquals($user->gallery_images[0], $user->cover_photo);

        // Upload dedicated Avatar
        $avatarFile = UploadedFile::fake()->image('avatar.png', 400, 400);
        $avatarRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/profile/upload-avatar', [
                'avatar' => $avatarFile,
            ]);

        $avatarRes->assertStatus(200)
            ->assertJsonPath('status', true);

        $user->refresh();
        $this->assertNotNull($user->avatar);
        $this->assertNotNull($user->avatar_url);
    }
}
