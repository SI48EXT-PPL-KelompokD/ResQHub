<?php

namespace Tests\Feature;

use App\Livewire\Auth\Register;
use App\Models\AidNeed;
use App\Models\User;
use App\Support\AppNavigation;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class BusinessFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function registerAs(string $role, array $extra = [])
    {
        $component = Livewire::test(Register::class)
            ->call('selectRole', $role)
            ->call('continueToDetails')
            ->set('name', 'Pengguna Uji')
            ->set('email', 'uji.'.$role.'@contoh.id')
            ->set('phone', '081299990000')
            ->set('password', 'rahasia123')
            ->set('password_confirmation', 'rahasia123');

        foreach ($extra as $field => $value) {
            $component->set($field, $value);
        }

        return $component->call('register');
    }

    public function test_beranda_publik_menampilkan_aksi_utama_tanpa_login(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Laporkan Kejadian')
            ->assertSee('Cari Posko Terdekat')
            ->assertSee('Masuk')
            ->assertSee('Daftar');
    }

    public function test_pendaftaran_admin_ditolak(): void
    {
        Livewire::test(Register::class)
            ->call('selectRole', 'admin')
            ->assertSet('role', '')
            ->call('continueToDetails')
            ->assertHasErrors('role')
            ->assertSet('step', 1);

        Livewire::test(Register::class)
            ->set('role', 'admin')
            ->set('step', 2)
            ->set('name', 'Penyusup')
            ->set('email', 'penyusup@contoh.id')
            ->set('phone', '081299990001')
            ->set('password', 'rahasia123')
            ->set('password_confirmation', 'rahasia123')
            ->call('register')
            ->assertHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'penyusup@contoh.id']);
    }

    public function test_warga_langsung_aktif_setelah_mendaftar(): void
    {
        $this->registerAs('warga')->assertHasNoErrors()->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'uji.warga@contoh.id', 'role' => 'warga', 'is_active' => true]);
        $this->assertAuthenticated();
    }

    public function test_koordinator_dan_nakes_menunggu_aktivasi_admin(): void
    {
        $this->registerAs('koordinator')->assertHasNoErrors()->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['email' => 'uji.koordinator@contoh.id', 'is_active' => false]);
        $this->assertGuest();

        $this->post('/login', ['login' => 'uji.koordinator@contoh.id', 'password' => 'rahasia123'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_lupa_kata_sandi_mengirim_tautan_dan_mengganti_sandi(): void
    {
        Notification::fake();
        $user = $this->user('warga.ahmad@resqhub.id');

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'sandibaru123',
            'password_confirmation' => 'sandibaru123',
        ])->assertRedirect(route('login'));

        $this->post('/login', ['login' => $user->email, 'password' => 'sandibaru123'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_menu_belum_tersedia_tetap_tampil_nonaktif_tanpa_label_modul(): void
    {
        $koordinator = $this->user('koordinator.budi@resqhub.id');

        $items = collect(AppNavigation::forUser($koordinator))->pluck('items')->flatten(1);

        $this->assertTrue($items->firstWhere('label', 'Penyaluran')['soon']);
        $this->assertNull($items->firstWhere('label', 'Penyaluran')['route']);

        $this->actingAs($koordinator)->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Penyaluran')
            ->assertSee('Segera')
            ->assertDontSee('Modul B')
            ->assertDontSee('Peta Publik (Haversine)');
    }

    public function test_dashboard_warga_tidak_menampilkan_nama_pemohon_sos(): void
    {
        $requester = User::where('role', 'warga')->where('email', '!=', 'warga.ahmad@resqhub.id')->first();

        $response = $this->actingAs($this->user('relawan.rian@resqhub.id'))->get('/admin/dashboard')->assertOk();

        $response->assertDontSee('Permintaan SOS terbaru');
        if ($requester) {
            $response->assertDontSee($requester->name);
        }
    }

    public function test_dashboard_setiap_peran_dapat_dibuka(): void
    {
        foreach (['admin', 'koordinator', 'nakes', 'relawan', 'donatur', 'warga'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail())->get('/admin/dashboard')->assertOk();
        }
    }

    private function donationUrl(): string
    {
        $need = AidNeed::where('status', 'terbuka')->whereRaw('qty_needed > qty_fulfilled + qty_pledged')->firstOrFail();

        return route('donor.goods-donations.create', ['aid_need' => $need->id]);
    }

    public function test_tamu_melihat_tombol_donasikan_di_papan_kebutuhan(): void
    {
        $this->get('/needs')->assertOk()->assertSee('Donasikan')->assertSee($this->donationUrl(), false);
    }

    public function test_peran_selain_donatur_tidak_melihat_tombol_donasikan(): void
    {
        $this->actingAs($this->user('koordinator.budi@resqhub.id'))
            ->get('/needs')
            ->assertOk()
            ->assertDontSee($this->donationUrl(), false);
    }

    public function test_tamu_yang_login_sebagai_donatur_kembali_ke_form_donasi(): void
    {
        $url = $this->donationUrl();

        $this->get($url)->assertRedirect(route('login'));
        $this->get(route('login'))->assertSee('Masuk dengan akun donatur');

        $this->post('/login', ['login' => 'donatur.anisa@resqhub.id', 'password' => 'password'])->assertRedirect($url);
        $this->get($url)->assertOk();
    }

    public function test_tamu_yang_login_bukan_donatur_diarahkan_ke_dashboard(): void
    {
        $this->get($this->donationUrl())->assertRedirect(route('login'));

        $this->post('/login', ['login' => 'warga.ahmad@resqhub.id', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_donatur_baru_kembali_ke_form_donasi_setelah_mendaftar(): void
    {
        $url = $this->donationUrl();
        session(['url.intended' => $url]);

        $this->registerAs('donatur')->assertHasNoErrors()->assertRedirect($url);
    }
}
