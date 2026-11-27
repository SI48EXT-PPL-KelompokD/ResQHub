<?php

namespace Tests\Feature;

use App\Livewire\Public\ShelterSearch;
use App\Models\DisasterEvent;
use App\Models\Shelter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicShelterSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_can_access_public_shelter_search_page_without_login(): void
    {
        $response = $this->get(route('public.shelters'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(ShelterSearch::class);
        $response->assertSee('Pencarian Posko Evakuasi Terdekat');
    }

    public function test_public_search_displays_shelters_sorted_by_haversine_distance(): void
    {
        $event = DisasterEvent::where('status', 'aktif')->first();
        $admin = User::where('role', 'admin')->first();

        // Titik acuan: Alun-Alun Bandung (-6.9218, 107.6074)
        $refLat = -6.9218;
        $refLng = 107.6074;

        // 1. Posko Sangat Dekat (~0.5 km)
        $near = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id' => $admin->id,
            'name' => 'Posko Alun-Alun Dekat',
            'address' => 'Jl. Asia Afrika No. 10',
            'lat' => -6.9210,
            'lng' => 107.6080,
            'capacity' => 100,
            'occupancy' => 20,
            'contact' => '08111',
            'status' => 'aktif',
        ]);

        // 2. Posko Menengah (~4.5 km)
        $mid = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id' => $admin->id,
            'name' => 'Posko Dago Menengah',
            'address' => 'Jl. Ir. H. Juanda No. 150',
            'lat' => -6.8820,
            'lng' => 107.6140,
            'capacity' => 150,
            'occupancy' => 50,
            'contact' => '08222',
            'status' => 'aktif',
        ]);

        // 3. Posko Jauh (~13 km)
        $far = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id' => $admin->id,
            'name' => 'Posko Lembang Jauh',
            'address' => 'Jl. Raya Lembang No. 200',
            'lat' => -6.8150,
            'lng' => 107.6180,
            'capacity' => 200,
            'occupancy' => 70,
            'contact' => '08333',
            'status' => 'aktif',
        ]);

        Livewire::test(ShelterSearch::class)
            ->call('setUserLocation', $refLat, $refLng, 'Alun-Alun Bandung')
            ->assertSeeInOrder([
                'Posko Alun-Alun Dekat',
                'Posko Dago Menengah',
                'Posko Lembang Jauh',
            ]);
    }

    public function test_public_search_filters_by_capacity_status(): void
    {
        $event = DisasterEvent::where('status', 'aktif')->first();
        $admin = User::where('role', 'admin')->first();

        // 1. Tersedia: okupansi < 80% (30 / 100 = 30%)
        $shelterTersedia = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id' => $admin->id,
            'name' => 'Posko Kapasitas Tersedia',
            'address' => 'Jl. Merdeka No. 1',
            'lat' => -6.910,
            'lng' => 107.610,
            'capacity' => 100,
            'occupancy' => 30,
            'contact' => '08123',
            'status' => 'aktif',
        ]);

        // 2. Hampir Penuh: okupansi 80% - 99% (85 / 100 = 85%)
        $shelterHampir = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id' => $admin->id,
            'name' => 'Posko Kapasitas Hampir Penuh',
            'address' => 'Jl. Riau No. 2',
            'lat' => -6.912,
            'lng' => 107.612,
            'capacity' => 100,
            'occupancy' => 85,
            'contact' => '08124',
            'status' => 'aktif',
        ]);

        // 3. Penuh: okupansi 100% (100 / 100 = 100%)
        $shelterPenuh = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id' => $admin->id,
            'name' => 'Posko Kapasitas Penuh Total',
            'address' => 'Jl. Cihampelas No. 3',
            'lat' => -6.914,
            'lng' => 107.614,
            'capacity' => 100,
            'occupancy' => 100,
            'contact' => '08125',
            'status' => 'penuh',
        ]);

        // Uji filter 'tersedia'
        Livewire::test(ShelterSearch::class)
            ->set('capacityFilter', 'tersedia')
            ->assertSee('Posko Kapasitas Tersedia')
            ->assertDontSee('Posko Kapasitas Hampir Penuh')
            ->assertDontSee('Posko Kapasitas Penuh Total');

        // Uji filter 'hampir_penuh'
        Livewire::test(ShelterSearch::class)
            ->set('capacityFilter', 'hampir_penuh')
            ->assertSee('Posko Kapasitas Hampir Penuh')
            ->assertDontSee('Posko Kapasitas Tersedia')
            ->assertDontSee('Posko Kapasitas Penuh Total');

        // Uji filter 'penuh'
        Livewire::test(ShelterSearch::class)
            ->set('capacityFilter', 'penuh')
            ->assertSee('Posko Kapasitas Penuh Total')
            ->assertDontSee('Posko Kapasitas Tersedia')
            ->assertDontSee('Posko Kapasitas Hampir Penuh');
    }

    public function test_public_search_filters_by_radius_distance(): void
    {
        $event = DisasterEvent::where('status', 'aktif')->first();
        $admin = User::where('role', 'admin')->first();

        $refLat = -6.9174;
        $refLng = 107.6191;

        // Dekat (~2 km)
        $near = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id' => $admin->id,
            'name' => 'Posko Dalam Radius 5km',
            'address' => 'Jl. Diponegoro No. 1',
            'lat' => -6.9000,
            'lng' => 107.6180,
            'capacity' => 100,
            'occupancy' => 10,
            'contact' => '08111',
            'status' => 'aktif',
        ]);

        // Jauh (~15 km)
        $far = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id' => $admin->id,
            'name' => 'Posko Luar Radius 5km',
            'address' => 'Jl. Soreang No. 100',
            'lat' => -7.0300,
            'lng' => 107.5200,
            'capacity' => 100,
            'occupancy' => 10,
            'contact' => '08222',
            'status' => 'aktif',
        ]);

        Livewire::test(ShelterSearch::class)
            ->call('setUserLocation', $refLat, $refLng, 'Pusat Kota')
            ->set('radiusFilter', '5')
            ->assertSee('Posko Dalam Radius 5km')
            ->assertDontSee('Posko Luar Radius 5km');
    }

    public function test_closed_shelters_are_excluded_from_default_operational_search(): void
    {
        $event = DisasterEvent::where('status', 'aktif')->first();
        $admin = User::where('role', 'admin')->first();

        $closedShelter = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id' => $admin->id,
            'name' => 'Posko Sudah Ditutup BPBD',
            'address' => 'Jl. Pasir Kaliki No. 99',
            'lat' => -6.910,
            'lng' => 107.600,
            'capacity' => 50,
            'occupancy' => 0,
            'contact' => '08999',
            'status' => 'ditutup',
            'close_reason' => 'Operasional selesai dan warga telah pulang.',
        ]);

        Livewire::test(ShelterSearch::class)
            ->assertDontSee('Posko Sudah Ditutup BPBD');
    }

    public function test_shelter_google_maps_direction_url_is_generated_correctly(): void
    {
        $shelter = new Shelter([
            'lat' => -6.9214,
            'lng' => 107.6075,
        ]);

        $expectedUrl = 'https://www.google.com/maps/dir/?api=1&destination=-6.9214,107.6075';
        $this->assertEquals($expectedUrl, $shelter->google_maps_url);
    }

    public function test_search_starts_from_the_first_active_event_location(): void
    {
        $event = DisasterEvent::active()->first();

        Livewire::test(ShelterSearch::class)
            ->assertSet('userLat', (float) $event->lat)
            ->assertSet('userLng', (float) $event->lng)
            ->assertSet('userLocationName', $event->name.' ('.$event->regency.')');
    }

    public function test_sort_by_name_and_remaining_capacity_override_distance_order(): void
    {
        $byName = Livewire::test(ShelterSearch::class)
            ->set('sortBy', 'name')
            ->viewData('shelters')
            ->pluck('name')
            ->all();

        $expectedByName = $byName;
        sort($expectedByName);
        $this->assertSame($expectedByName, $byName);

        $byCapacity = Livewire::test(ShelterSearch::class)
            ->set('sortBy', 'capacity')
            ->viewData('shelters')
            ->map(fn (Shelter $shelter) => $shelter->remaining_capacity)
            ->all();

        $expectedByCapacity = $byCapacity;
        rsort($expectedByCapacity);
        $this->assertSame($expectedByCapacity, $byCapacity);
    }

    public function test_map_events_send_named_coordinates_to_the_browser(): void
    {
        $shelter = Shelter::first();

        Livewire::test(ShelterSearch::class)
            ->call('selectShelter', $shelter->id)
            ->assertDispatched('focus-shelter-marker', id: $shelter->id, lat: (float) $shelter->lat, lng: (float) $shelter->lng)
            ->call('setUserLocation', -6.9, 107.6, 'Titik Uji')
            ->assertDispatched('user-location-updated', lat: -6.9, lng: 107.6, name: 'Titik Uji');
    }

    public function test_map_markers_follow_the_active_filter(): void
    {
        Shelter::where('id', '!=', Shelter::first()->id)->update(['name' => 'Posko Lain']);
        $target = Shelter::first();

        Livewire::test(ShelterSearch::class)
            ->set('search', $target->name)
            ->assertDispatched('shelter-markers-updated', function (string $name, array $params) use ($target) {
                return count($params['markers']) === 1 && $params['markers'][0]['id'] === $target->id;
            });
    }

    public function test_invalid_user_coordinates_are_ignored(): void
    {
        Livewire::test(ShelterSearch::class)
            ->call('setUserLocation', 120.0, 500.0, 'Titik Salah')
            ->assertNotSet('userLocationName', 'Titik Salah');
    }

    public function test_contact_phone_extracts_a_callable_number(): void
    {
        $this->assertSame('081234567802', (new Shelter(['contact' => '081234567802 (Pak Budi)']))->contact_phone);
        $this->assertSame('+628123456789', (new Shelter(['contact' => '+62 812-3456-789']))->contact_phone);
        $this->assertNull((new Shelter(['contact' => 'Pak Budi']))->contact_phone);
    }

    public function test_haversine_formula_calculates_accurate_kilometers(): void
    {
        // Jarak Monas Jakarta (-6.175392, 106.827153) ke Gedung Sate Bandung (-6.902484, 107.618810)
        // Jarak sebenarnya adalah ~119 km
        $distance = Shelter::calculateDistance(-6.175392, 106.827153, -6.902484, 107.618810);

        $this->assertGreaterThan(115.0, $distance);
        $this->assertLessThan(125.0, $distance);
    }
}
