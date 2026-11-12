<?php

namespace Tests\Feature;

use App\Models\DisasterEvent;
use App\Models\Shelter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Admin\ShelterManagement;

class ShelterManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_koordinator_can_access_shelter_management_page(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();

        $response = $this->actingAs($koordinator)->get('/admin/shelters');

        $response->assertStatus(200);
        $response->assertSee('Posko Evakuasi');
        $response->assertSee('PBI-04');
    }

    public function test_admin_can_access_shelter_management_page(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/shelters');

        $response->assertStatus(200);
        $response->assertSee('Posko Evakuasi');
    }

    public function test_warga_cannot_access_shelter_management_page(): void
    {
        $warga = User::where('role', 'warga')->first();

        $response = $this->actingAs($warga)->get('/admin/shelters');

        $response->assertStatus(403);
    }

    public function test_koordinator_can_create_new_shelter_with_valid_data(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $activeEvent = DisasterEvent::where('status', 'aktif')->first();

        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->set('disaster_event_id', $activeEvent->id)
            ->set('name', 'Posko Pengungsian Balai Warga RW 05')
            ->set('address', 'Jl. Sukamenak Indah No. 12')
            ->set('lat', -6.9500000)
            ->set('lng', 107.5900000)
            ->set('capacity', 120)
            ->set('contact', '081299887766 (Bpk. Mulyadi)')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('shelters', [
            'name'           => 'Posko Pengungsian Balai Warga RW 05',
            'coordinator_id' => $koordinator->id,
            'capacity'       => 120,
            'occupancy'      => 0,
            'status'         => 'aktif',
        ]);
    }

    public function test_shelter_creation_fails_if_capacity_is_zero_or_negative(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $activeEvent = DisasterEvent::where('status', 'aktif')->first();

        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->set('disaster_event_id', $activeEvent->id)
            ->set('name', 'Posko Rusak')
            ->set('address', 'Jl. Test')
            ->set('lat', -6.95)
            ->set('lng', 107.59)
            ->set('capacity', 0)
            ->set('contact', '08123')
            ->call('store')
            ->assertHasErrors(['capacity']);
    }

    public function test_capacity_status_and_occupancy_percentage_calculation(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $event = DisasterEvent::where('status', 'aktif')->first();

        // 1. Posko dengan okupansi 50/100 (50% -> Tersedia)
        $shelterTersedia = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko A (Tersedia)',
            'address'           => 'Alamat A',
            'lat'               => -6.9,
            'lng'               => 107.6,
            'capacity'          => 100,
            'occupancy'         => 50,
            'contact'           => '08123',
            'status'            => 'aktif',
        ]);

        $this->assertEquals(50.0, $shelterTersedia->occupancy_percentage);
        $this->assertEquals('tersedia', $shelterTersedia->capacity_status);
        $this->assertEquals(50, $shelterTersedia->remaining_capacity);

        // 2. Posko dengan okupansi 85/100 (85% -> Hampir Penuh)
        $shelterHampirPenuh = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko B (Hampir Penuh)',
            'address'           => 'Alamat B',
            'lat'               => -6.91,
            'lng'               => 107.61,
            'capacity'          => 100,
            'occupancy'         => 85,
            'contact'           => '08123',
            'status'            => 'aktif',
        ]);

        $this->assertEquals(85.0, $shelterHampirPenuh->occupancy_percentage);
        $this->assertEquals('hampir_penuh', $shelterHampirPenuh->capacity_status);
        $this->assertEquals(15, $shelterHampirPenuh->remaining_capacity);

        // 3. Posko dengan okupansi 100/100 (100% -> Penuh)
        $shelterPenuh = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko C (Penuh)',
            'address'           => 'Alamat C',
            'lat'               => -6.92,
            'lng'               => 107.62,
            'capacity'          => 100,
            'occupancy'         => 100,
            'contact'           => '08123',
            'status'            => 'penuh',
        ]);

        $this->assertEquals(100.0, $shelterPenuh->occupancy_percentage);
        $this->assertEquals('penuh', $shelterPenuh->capacity_status);
        $this->assertEquals(0, $shelterPenuh->remaining_capacity);
    }

    public function test_koordinator_can_update_occupancy_realtime_and_sync_status(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $event = DisasterEvent::where('status', 'aktif')->first();

        $shelter = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko Uji Okupansi',
            'address'           => 'Jl. Uji No. 1',
            'lat'               => -6.93,
            'lng'               => 107.63,
            'capacity'          => 100,
            'occupancy'         => 20,
            'contact'           => '08123',
            'status'            => 'aktif',
        ]);

        // Koordinator buka modal dan update okupansi menjadi 100 (mencapai kapasitas maksimal)
        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->call('openOccupancyModal', $shelter->id)
            ->assertSet('selectedShelterId', $shelter->id)
            ->assertSet('newOccupancy', 20)
            ->set('newOccupancy', 100)
            ->call('updateOccupancy')
            ->assertHasNoErrors()
            ->assertSet('showOccupancyModal', false);

        $shelter->refresh();
        $this->assertEquals(100, $shelter->occupancy);
        // Status otomatis menjadi 'penuh' karena okupansi mencapai 100%
        $this->assertEquals('penuh', $shelter->status);
        $this->assertEquals('penuh', $shelter->capacity_status);

        // Jika okupansi diturunkan kembali di bawah kapasitas (misal 60), status otomatis kembali 'aktif'
        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->call('openOccupancyModal', $shelter->id)
            ->set('newOccupancy', 60)
            ->call('updateOccupancy')
            ->assertHasNoErrors();

        $shelter->refresh();
        $this->assertEquals(60, $shelter->occupancy);
        $this->assertEquals('aktif', $shelter->status);
        $this->assertEquals('tersedia', $shelter->capacity_status);
    }

    public function test_filtering_shelters_by_capacity_status(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $event = DisasterEvent::where('status', 'aktif')->first();

        // Bersihkan shelter lama agar pengujian filter presisi
        Shelter::where('coordinator_id', $koordinator->id)->delete();

        $shelter1 = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko Hijau Tersedia',
            'address'           => 'Alamat 1',
            'lat'               => -6.9,
            'lng'               => 107.6,
            'capacity'          => 100,
            'occupancy'         => 30, // 30% -> Tersedia
            'contact'           => '081',
            'status'            => 'aktif',
        ]);

        $shelter2 = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko Kuning Hampir Penuh',
            'address'           => 'Alamat 2',
            'lat'               => -6.91,
            'lng'               => 107.61,
            'capacity'          => 100,
            'occupancy'         => 90, // 90% -> Hampir Penuh
            'contact'           => '082',
            'status'            => 'aktif',
        ]);

        // Filter 'hampir_penuh'
        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->set('capacityFilter', 'hampir_penuh')
            ->assertSee('Posko Kuning Hampir Penuh')
            ->assertDontSee('Posko Hijau Tersedia');

        // Filter 'tersedia'
        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->set('capacityFilter', 'tersedia')
            ->assertSee('Posko Hijau Tersedia')
            ->assertDontSee('Posko Kuning Hampir Penuh');
    }

    public function test_koordinator_can_edit_own_shelter_with_valid_data(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $event = DisasterEvent::where('status', 'aktif')->first();

        $shelter = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko Awal',
            'address'           => 'Jl. Awal No. 10',
            'lat'               => -6.9900000,
            'lng'               => 107.6200000,
            'capacity'          => 150,
            'occupancy'         => 30,
            'contact'           => '081234567890',
            'status'            => 'aktif',
        ]);

        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->call('openEditModal', $shelter->id)
            ->assertSet('editingShelterId', $shelter->id)
            ->assertSet('edit_name', 'Posko Awal')
            ->assertSet('edit_capacity', 150)
            ->set('edit_name', 'Posko Gedung Serbaguna Diperbarui')
            ->set('edit_address', 'Jl. Baru No. 99')
            ->set('edit_capacity', 200)
            ->set('edit_contact', '089988776655 (Admin)')
            ->call('update')
            ->assertHasNoErrors()
            ->assertSet('showEditModal', false);

        $shelter->refresh();
        $this->assertEquals('Posko Gedung Serbaguna Diperbarui', $shelter->name);
        $this->assertEquals('Jl. Baru No. 99', $shelter->address);
        $this->assertEquals(200, $shelter->capacity);
        $this->assertEquals('089988776655 (Admin)', $shelter->contact);
    }

    public function test_capacity_cannot_be_less_than_active_refugees_count(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $event = DisasterEvent::where('status', 'aktif')->first();

        // Posko saat ini menampung 75 pengungsi aktif dengan kapasitas 100
        $shelter = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko Uji Batas Kapasitas',
            'address'           => 'Jl. Batas No. 5',
            'lat'               => -6.991,
            'lng'               => 107.621,
            'capacity'          => 100,
            'occupancy'         => 75,
            'contact'           => '08123',
            'status'            => 'aktif',
        ]);

        // Koordinator mencoba mengubah kapasitas menjadi 50 (lebih kecil dari okupansi aktif 75 jiwa)
        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->call('openEditModal', $shelter->id)
            ->set('edit_capacity', 50)
            ->call('update')
            ->assertHasErrors(['edit_capacity'])
            ->assertSet('showEditModal', true);

        // Kapasitas posko di database tidak boleh berubah
        $shelter->refresh();
        $this->assertEquals(100, $shelter->capacity);
    }

    public function test_koordinator_cannot_edit_other_koordinator_shelter(): void
    {
        $koordinatorA = User::where('role', 'koordinator')->first();
        $event = DisasterEvent::where('status', 'aktif')->first();

        // Buat koordinator B
        $koordinatorB = User::factory()->create([
            'role'  => 'koordinator',
            'email' => 'koordinator_b@example.com',
        ]);

        $shelterA = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinatorA->id,
            'name'              => 'Posko Milik Koordinator A',
            'address'           => 'Jl. Koordinator A',
            'lat'               => -6.992,
            'lng'               => 107.622,
            'capacity'          => 100,
            'occupancy'         => 10,
            'contact'           => '08123',
            'status'            => 'aktif',
        ]);

        // Koordinator B mencoba mengakses posko milik Koordinator A
        Livewire::actingAs($koordinatorB)
            ->test(ShelterManagement::class)
            ->call('openEditModal', $shelterA->id)
            ->assertStatus(404);
    }

    public function test_admin_can_edit_any_shelter(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $admin = User::where('role', 'admin')->first();
        $event = DisasterEvent::where('status', 'aktif')->first();

        $shelter = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko Binaan Koordinator',
            'address'           => 'Jl. Lapangan',
            'lat'               => -6.993,
            'lng'               => 107.623,
            'capacity'          => 80,
            'occupancy'         => 20,
            'contact'           => '08111222',
            'status'            => 'aktif',
        ]);

        // Admin dapat membuka dan mengubah data posko milik koordinator siapapun
        Livewire::actingAs($admin)
            ->test(ShelterManagement::class)
            ->call('openEditModal', $shelter->id)
            ->set('edit_capacity', 120)
            ->call('update')
            ->assertHasNoErrors()
            ->assertSet('showEditModal', false);

        $shelter->refresh();
        $this->assertEquals(120, $shelter->capacity);
    }

    public function test_shelter_cannot_be_closed_if_refugees_are_still_active(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $event = DisasterEvent::where('status', 'aktif')->first();

        // Posko masih menampung 40 jiwa pengungsi aktif
        $shelter = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko Masih Ada Pengungsi',
            'address'           => 'Jl. Pengungsi No. 3',
            'lat'               => -6.994,
            'lng'               => 107.624,
            'capacity'          => 100,
            'occupancy'         => 40,
            'contact'           => '081234',
            'status'            => 'aktif',
        ]);

        // Koordinator mencoba mengubah status menjadi 'ditutup'
        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->call('openEditModal', $shelter->id)
            ->set('edit_status', 'ditutup')
            ->set('edit_close_reason', 'Bencana telah dinyatakan selesai.')
            ->call('update')
            ->assertHasErrors(['edit_status'])
            ->assertSet('showEditModal', true);

        // Status di database harus tetap aktif
        $shelter->refresh();
        $this->assertEquals('aktif', $shelter->status);
        $this->assertNull($shelter->close_reason);
    }

    public function test_shelter_can_be_closed_if_no_active_refugees_and_reason_provided(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $event = DisasterEvent::where('status', 'aktif')->first();

        // Posko dengan pengungsi 0 jiwa (sudah pulang / kosong)
        $shelter = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko Siap Ditutup',
            'address'           => 'Jl. Evakuasi Selesai',
            'lat'               => -6.995,
            'lng'               => 107.625,
            'capacity'          => 100,
            'occupancy'         => 0,
            'contact'           => '0812345',
            'status'            => 'aktif',
        ]);

        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->call('openEditModal', $shelter->id)
            ->set('edit_status', 'ditutup')
            ->set('edit_close_reason', 'Masa tanggap darurat telah berakhir dan seluruh pengungsi telah kembali ke rumah.')
            ->call('update')
            ->assertHasNoErrors()
            ->assertSet('showEditModal', false);

        $shelter->refresh();
        $this->assertEquals('ditutup', $shelter->status);
        $this->assertEquals('ditutup', $shelter->capacity_status);
        $this->assertEquals('Masa tanggap darurat telah berakhir dan seluruh pengungsi telah kembali ke rumah.', $shelter->close_reason);
    }

    public function test_closing_shelter_fails_if_close_reason_is_empty(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $event = DisasterEvent::where('status', 'aktif')->first();

        $shelter = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko Tutup Tanpa Alasan',
            'address'           => 'Jl. Tanpa Alasan',
            'lat'               => -6.996,
            'lng'               => 107.626,
            'capacity'          => 80,
            'occupancy'         => 0,
            'contact'           => '08123',
            'status'            => 'aktif',
        ]);

        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->call('openEditModal', $shelter->id)
            ->set('edit_status', 'ditutup')
            ->set('edit_close_reason', '')
            ->call('update')
            ->assertHasErrors(['edit_close_reason'])
            ->assertSet('showEditModal', true);

        $shelter->refresh();
        $this->assertEquals('aktif', $shelter->status);
    }

    public function test_shelter_without_refugees_needs_or_stocks_can_be_deleted(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $event = DisasterEvent::where('status', 'aktif')->first();

        // Posko baru tanpa data historis pengungsi, kebutuhan, atau stok
        $shelter = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko Baru Salah Input',
            'address'           => 'Jl. Salah Input',
            'lat'               => -6.997,
            'lng'               => 107.627,
            'capacity'          => 50,
            'occupancy'         => 0,
            'contact'           => '08111',
            'status'            => 'aktif',
        ]);

        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->call('confirmDelete', $shelter->id)
            ->assertSet('deletingShelterId', $shelter->id)
            ->assertSet('deletingShelterCanBeDeleted', true)
            ->call('destroy')
            ->assertHasNoErrors()
            ->assertSet('showDeleteModal', false);

        $this->assertDatabaseMissing('shelters', [
            'id' => $shelter->id,
        ]);
    }

    public function test_shelter_with_historical_refugees_or_needs_or_stocks_cannot_be_deleted(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $event = DisasterEvent::where('status', 'aktif')->first();

        $shelter = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko Bersejarah',
            'address'           => 'Jl. Bersejarah No. 1',
            'lat'               => -6.998,
            'lng'               => 107.628,
            'capacity'          => 100,
            'occupancy'         => 0, // sedang 0 jiwa, tapi memiliki riwayat di tabel refugees
            'contact'           => '08222',
            'status'            => 'aktif',
        ]);

        // Simulasikan riwayat pengungsi di database
        DB::table('refugees')->insert([
            'shelter_id'    => $shelter->id,
            'head_name'     => 'Bapak Supardi',
            'nik'           => '3204123456780001',
            'members'       => 4,
            'status'        => 'pulang',
            'checked_in_at' => now()->subDays(5),
            'created_at'    => now()->subDays(5),
            'updated_at'    => now()->subDays(1),
        ]);

        // Koordinator mencoba menghapus posko ini
        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->call('confirmDelete', $shelter->id)
            ->assertSet('deletingShelterId', $shelter->id)
            ->assertSet('deletingShelterCanBeDeleted', false)
            ->call('destroy')
            ->assertSet('showDeleteModal', false);

        // Posko tetap ada di database dan tidak terhapus
        $this->assertDatabaseHas('shelters', [
            'id' => $shelter->id,
        ]);
    }
}
