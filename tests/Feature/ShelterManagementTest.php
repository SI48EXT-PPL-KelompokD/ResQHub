<?php

namespace Tests\Feature;

use App\Models\DisasterEvent;
use App\Models\Shelter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
