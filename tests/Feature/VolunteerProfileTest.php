<?php

namespace Tests\Feature;

use App\Livewire\Volunteer\ProfileManager;
use App\Models\User;
use App\Models\VolunteerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VolunteerProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function relawanAktif(): User
    {
        return User::where('email', 'relawan.rian@resqhub.id')->firstOrFail();
    }

    private function relawanTanpaTugasAktif(): User
    {
        return User::where('email', 'relawan.agus@resqhub.id')->firstOrFail();
    }

    public function test_dashboard_relawan_menampilkan_komponen_profil_keahlian(): void
    {
        $this->actingAs($this->relawanAktif())
            ->get('/admin/dashboard')
            ->assertStatus(200)
            ->assertSeeLivewire('volunteer.profile-manager');
    }

    public function test_dashboard_warga_tidak_menampilkan_komponen_profil_keahlian(): void
    {
        $warga = User::where('role', 'warga')->firstOrFail();

        $this->actingAs($warga)
            ->get('/admin/dashboard')
            ->assertStatus(200)
            ->assertDontSeeLivewire('volunteer.profile-manager');
    }

    public function test_panel_profil_menampilkan_data_relawan(): void
    {
        $this->actingAs($this->relawanAktif());

        Livewire::test(ProfileManager::class)
            ->call('openProfile')
            ->assertSet('open', true)
            ->assertSee('Profil Keahlian')
            ->assertSee('Evakuasi & SAR')
            ->assertSee('Transportasi & Distribusi');
    }

    public function test_relawan_dapat_menyimpan_profil_keahlian(): void
    {
        $relawan = $this->relawanAktif();
        $this->actingAs($relawan);

        Livewire::test(ProfileManager::class)
            ->call('openProfile')
            ->call('edit')
            ->set('regency', 'Kota Bandung')
            ->set('radius_km', 30)
            ->set('availability', 'tidak_siap')
            ->set('selectedSkills', [1, 3])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('mode', 'view')
            ->assertSet('open', true)
            ->assertSee('Profil keahlian berhasil disimpan.');

        $profile = $relawan->fresh()->volunteerProfile;

        $this->assertSame(30, $profile->radius_km);
        $this->assertSame('tidak_siap', $profile->availability);
        $this->assertSame('Kota Bandung', $relawan->fresh()->regency);
        $this->assertEqualsCanonicalizing([1, 3], $profile->skills()->pluck('skills.id')->all());
    }

    public function test_keahlian_medis_wajib_mengisi_nomor_str(): void
    {
        $this->actingAs($this->relawanAktif());

        Livewire::test(ProfileManager::class)
            ->call('openProfile')
            ->call('edit')
            ->set('selectedSkills', [4])
            ->set('str_number', '')
            ->call('save')
            ->assertHasErrors(['str_number' => 'required']);
    }

    public function test_profil_tidak_dapat_dihapus_saat_ada_tugas_berjalan(): void
    {
        $relawan = $this->relawanAktif();
        $this->actingAs($relawan);

        Livewire::test(ProfileManager::class)
            ->call('openProfile')
            ->call('requestDelete')
            ->assertSet('showDeleteModal', false)
            ->assertSee('tidak dapat dihapus');

        $this->assertNotNull($relawan->fresh()->volunteerProfile);
    }

    public function test_profil_tanpa_tugas_berjalan_dapat_dihapus_soft_delete(): void
    {
        $relawan = $this->relawanTanpaTugasAktif();
        $this->actingAs($relawan);

        Livewire::test(ProfileManager::class)
            ->call('openProfile')
            ->call('requestDelete')
            ->assertSet('showDeleteModal', true)
            ->call('deleteProfile')
            ->assertSet('showDeleteModal', false)
            ->assertSee('Profil relawan berhasil dihapus.');

        $this->assertSoftDeleted('volunteer_profiles', ['user_id' => $relawan->id]);
        $this->assertSame(0, VolunteerProfile::where('user_id', $relawan->id)->count());
    }
}
