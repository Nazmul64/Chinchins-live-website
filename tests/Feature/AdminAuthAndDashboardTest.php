<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_rendered_at_root(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Onedash');
        $response->assertSee('admin@gmail.com');
    }

    public function test_admin_can_login_with_default_credentials(): void
    {
        $user = User::create([
            'name' => 'Jhon Deo',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin@gmail.com'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@gmail.com',
            'password' => 'admin@gmail.com',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_access_dashboard(): void
    {
        $user = User::create([
            'name' => 'Jhon Deo',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin@gmail.com'),
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Color Dashboard 1');
        $response->assertSee('Total Orders');
        $response->assertSee('8,542');
        $response->assertSee('Revenue');
        $response->assertSee('By Device');
        $response->assertSee('Traffic Source');
    }

    public function test_admin_can_logout(): void
    {
        $user = User::create([
            'name' => 'Jhon Deo',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin@gmail.com'),
        ]);

        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
