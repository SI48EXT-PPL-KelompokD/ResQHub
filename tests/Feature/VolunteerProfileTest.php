<?php

namespace Tests\Feature;

use App\Livewire\Volunteer\ProfileManager;
use App\Models\User;
use App\Models\VolunteerProfile;
use App\Models\VolunteerTask;
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
        $relawan = User::factory()->create([
            'role' => 'relawan',
            'regency' => 'Kabupaten Bandung',
        ]);

        $profile = $relawan->volunteerProfile()->create([
            'domicile_lat' => -6.9915,
            'domicile_lng' => 107.6300,
            'radius_km' => 15,
            'availability' => 'siap',
        ]);

        $profile->skills()->attach(7);

        return $relawan;
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
            ->assertSee('Profil Saya')
            ->assertSee('Siap Ditugaskan')
            ->assertSee('Evakuasi & SAR')
            ->assertSee('Transportasi & Distribusi')
            ->assertSee('Riwayat tugas');
    }

    public function test_riwayat_tugas_menampilkan_setiap_tugas_relawan(): void
    {
        $this->actingAs($this->relawanAktif());

        Livewire::test(ProfileManager::class)
            ->call('openProfile')
            ->assertSee('Riwayat tugas')
            ->assertSee('Evakuasi Perahu Karet Warga Terjebak RW 07 Andir')
            ->assertSee('Berjalan')
            ->assertSee('sedang berjalan');
    }

    public function test_riwayat_tugas_tetap_tampil_saat_belum_ada_tugas(): void
    {
        $relawan = User::factory()->create([
            'role' => 'relawan',
            'regency' => 'Kota Bandung',
        ]);
        $relawan->volunteerProfile()->create([
            'radius_km' => 10,
            'availability' => 'siap',
        ]);

        $this->actingAs($relawan);

        Livewire::test(ProfileManager::class)
            ->call('openProfile')
            ->assertSee('Riwayat tugas')
            ->assertSee('Belum ada riwayat tugas');
    }

    public function test_relawan_dapat_menyimpan_profil_keahlian(): void
    {
        $relawan = $this->relawanAktif();
        $this->actingAs($relawan);

        Livewire::test(ProfileManager::class)
            ->call('openProfile')
            ->call('edit')
            ->assertSee('Profil Keahlian')
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

    public function test_relawan_tanpa_profil_dapat_membuat_profil_baru(): void
    {
        $user = User::create([
            'name' => 'Relawan Baru',
            'email' => 'relawan.baru@resqhub.id',
            'password' => 'password',
            'role' => 'relawan',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(ProfileManager::class)
            ->call('openProfile')
            ->assertSee('Belum Ada Profil')
            ->call('edit')
            ->assertSee('Profil Keahlian')
            ->set('regency', 'Kota Bandung')
            ->set('radius_km', 12)
            ->set('availability', 'siap')
            ->set('selectedSkills', [1, 2])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('mode', 'view')
            ->assertSee('Siap Ditugaskan');

        $this->assertDatabaseHas('volunteer_profiles', [
            'user_id' => $user->id,
            'radius_km' => 12,
            'availability' => 'siap',
        ]);

        $profile = $user->fresh()->volunteerProfile;
        $this->assertEqualsCanonicalizing([1, 2], $profile->skills()->pluck('skills.id')->all());
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

    public function test_profil_dengan_tugas_berjalan_tidak_dapat_dihapus(): void
    {
        $relawan = $this->relawanAktif();
        $this->actingAs($relawan);

        $profileId = $relawan->volunteerProfile->id;

        Livewire::test(ProfileManager::class)
            ->call('openProfile')
            ->call('requestDelete')
            ->assertSet('showDeleteModal', false)
            ->assertSet('notificationType', 'error')
            ->assertSee('tidak dapat dihapus selama masih ada tugas berjalan')
            ->call('deleteProfile')
            ->assertSet('showDeleteModal', false);

        $this->assertNotNull($relawan->fresh()->volunteerProfile);
        $this->assertDatabaseHas('volunteer_profiles', ['id' => $profileId, 'deleted_at' => null]);
        $this->assertDatabaseHas('task_assignments', [
            'volunteer_task_id' => 1,
            'volunteer_profile_id' => $profileId,
            'status' => 'diterima',
        ]);
    }

    public function test_riwayat_tugas_selesai_tetap_tersimpan_setelah_profil_dihapus(): void
    {
        $relawan = $this->relawanTanpaTugasAktif();
        $profileId = $relawan->volunteerProfile->id;

        $task = VolunteerTask::create([
            'disaster_event_id' => 1,
            'skill_id' => 7,
            'title' => 'Distribusi Logistik Selesai',
            'description' => 'Tugas yang sudah selesai.',
            'lat' => -6.9961,
            'lng' => 107.6238,
            'quota' => 1,
            'start_at' => now()->subDays(3),
            'end_at' => now()->subDays(3)->addHours(3),
            'status' => 'selesai',
            'created_by' => User::where('role', 'koordinator')->value('id'),
        ]);

        $assignment = $task->assignments()->create([
            'volunteer_profile_id' => $profileId,
            'status' => 'selesai',
        ]);

        $this->actingAs($relawan);

        Livewire::test(ProfileManager::class)
            ->call('openProfile')
            ->assertSee('Distribusi Logistik Selesai')
            ->call('requestDelete')
            ->call('deleteProfile')
            ->assertSee('Riwayat tugas yang sudah selesai tetap tersimpan.');

        $this->assertSoftDeleted('volunteer_profiles', ['id' => $profileId]);
        $this->assertDatabaseHas('task_assignments', [
            'id' => $assignment->id,
            'volunteer_profile_id' => $profileId,
            'status' => 'selesai',
        ]);
    }

    public function test_profil_tanpa_tugas_berjalan_dapat_dihapus_soft_delete(): void
    {
        $relawan = $this->relawanTanpaTugasAktif();
        $this->actingAs($relawan);

        Livewire::test(ProfileManager::class)
            ->call('openProfile')
            ->call('requestDelete')
            ->assertSet('showDeleteModal', true)
            ->assertSee('Hapus profil relawan?')
            ->call('deleteProfile')
            ->assertSet('showDeleteModal', false)
            ->assertSee('Profil relawan berhasil dihapus.');

        $this->assertSoftDeleted('volunteer_profiles', ['user_id' => $relawan->id]);
        $this->assertSame(0, VolunteerProfile::where('user_id', $relawan->id)->count());
    }
}
