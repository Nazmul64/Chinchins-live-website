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
        $this->assertGreaterThanOrEqual(8, strlen($user->account_id));
        $this->assertEquals($user->account_id, $user->display_id);
        $this->assertEquals($user->account_id, $user->uid);
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

    public function test_user_can_upload_and_delete_avatar(): void
    {
        $user = User::create([
            'first_name' => 'Nazmul',
            'last_name'  => 'Hossain',
            'phone'      => '01706640864',
            'email'      => 'nazmul@gmail.com',
            'password'   => bcrypt('secret123'),
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

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

        // Delete Avatar
        $delRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/profile/delete-avatar');

        $delRes->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.avatar', null)
            ->assertJsonPath('data.avatar_url', null);

        $user->refresh();
        $this->assertNull($user->avatar);
        $this->assertNull($user->avatar_url);
    }

    public function test_user_can_upload_and_delete_cover_photo(): void
    {
        $user = User::create([
            'first_name' => 'Nazmul',
            'last_name'  => 'Hossain',
            'phone'      => '01706640864',
            'email'      => 'nazmul@gmail.com',
            'password'   => bcrypt('secret123'),
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        // Upload dedicated Cover
        $coverFile = UploadedFile::fake()->image('cover.jpg', 800, 400);
        $coverRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/profile/upload-cover', [
                'cover_photo' => $coverFile,
            ]);

        $coverRes->assertStatus(200)
            ->assertJsonPath('status', true);

        $user->refresh();
        $this->assertNotNull($user->cover_photo);
        $this->assertNotNull($user->cover_photo_url);

        // Delete Cover
        $delRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/profile/delete-cover');

        $delRes->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.cover_photo', null)
            ->assertJsonPath('data.cover_photo_url', null);

        $user->refresh();
        $this->assertNull($user->cover_photo);
        $this->assertNull($user->cover_photo_url);
    }

    public function test_user_can_upload_and_delete_gallery_photos(): void
    {
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

        $firstPhoto = $user->gallery_images[0];

        // Delete single photo by path
        $delRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/profile/delete-photo', [
                'photo' => $firstPhoto,
            ]);

        $delRes->assertStatus(200)
            ->assertJsonPath('status', true);

        $user->refresh();
        $this->assertCount(1, $user->gallery_images);
        $this->assertFalse(in_array($firstPhoto, $user->gallery_images));

        // Clear gallery
        $clearRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/profile/clear-gallery');

        $clearRes->assertStatus(200)
            ->assertJsonPath('status', true);

        $user->refresh();
        $this->assertCount(0, $user->gallery_images);
    }

    public function test_user_profiles_and_media_are_isolated_per_user(): void
    {
        $userA = User::create([
            'first_name' => 'User',
            'last_name'  => 'A',
            'phone'      => '01700000001',
            'email'      => 'userA@test.com',
            'password'   => bcrypt('secret123'),
        ]);

        $userB = User::create([
            'first_name' => 'User',
            'last_name'  => 'B',
            'phone'      => '01700000002',
            'email'      => 'userB@test.com',
            'password'   => bcrypt('secret123'),
        ]);

        $tokenA = $userA->createToken('token_a')->plainTextToken;
        $tokenB = $userB->createToken('token_b')->plainTextToken;

        // User A uploads avatar
        $avatarFile = UploadedFile::fake()->image('userA_avatar.png', 400, 400);
        $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->postJson('/api/profile/upload-avatar', [
                'avatar' => $avatarFile,
            ])
            ->assertStatus(200);

        $userA->refresh();
        $userB->refresh();

        $this->assertNotNull($userA->avatar);
        $this->assertNull($userB->avatar);
        $this->assertNull($userB->avatar_url);

        // User B views own profile via /me
        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $tokenB)
            ->getJson('/api/profile/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('data.user.id', $userB->id)
            ->assertJsonPath('data.user.avatar_url', null)
            ->assertJsonPath('data.user.gallery_images', []);
    }

    public function test_search_user_by_eight_digit_account_id(): void
    {
        $user = User::create([
            'first_name' => 'Searchable',
            'last_name'  => 'Person',
            'phone'      => '01799887766',
            'email'      => 'searchable@test.com',
            'account_id' => '84920183',
            'password'   => bcrypt('secret123'),
        ]);

        $response = $this->getJson('/api/search?q=84920183');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ])
            ->assertJsonFragment([
                'account_id' => '84920183',
                'display_id' => '84920183',
            ]);
    }
}
