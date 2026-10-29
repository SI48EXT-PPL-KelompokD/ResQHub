<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk ke akun Anda');
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Registrasi Akun Baru');
        $response->assertSee('Pilih Peran / Hak Akses Akun');
    }

    public function test_admin_can_access_dashboard_and_user_management(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');

        $usersResponse = $this->actingAs($admin)->get('/admin/users');
        $usersResponse->assertStatus(200);
    }

    public function test_relawan_can_access_dashboard_with_proper_sidebar(): void
    {
        $relawan = User::where('role', 'relawan')->first();

        $response = $this->actingAs($relawan)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Tugas Saya');
        $response->assertSee('SOS Masuk');
        $response->assertDontSee('Manajemen Pengguna');
    }

    public function test_warga_cannot_access_user_management(): void
    {
        $warga = User::where('role', 'warga')->first();

        $response = $this->actingAs($warga)->get('/admin/users');
        $response->assertStatus(403);
    }
}
