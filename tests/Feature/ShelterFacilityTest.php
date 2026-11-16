<?php

namespace Tests\Feature;

use App\Livewire\Admin\ShelterManagement;
use App\Livewire\Public\ShelterSearch;
use App\Models\DisasterEvent;
use App\Models\Shelter;
use App\Models\ShelterFacility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShelterFacilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_shelter_has_many_facilities_and_facility_belongs_to_shelter(): void
    {
        $shelter = Shelter::first();
        $this->assertNotNull($shelter);
        $this->assertGreaterThan(0, $shelter->facilities()->count());

        $facility = $shelter->facilities()->first();
        $this->assertInstanceOf(ShelterFacility::class, $facility);
        $this->assertInstanceOf(Shelter::class, $facility->shelter);
        $this->assertEquals($shelter->id, $facility->shelter->id);
    }

    public function test_facility_labels_icons_and_condition_scopes_work_correctly(): void
    {
        $event = DisasterEvent::where('status', 'aktif')->first();
        $koordinator = User::where('role', 'koordinator')->first();

        $shelter = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $koordinator->id,
            'name'              => 'Posko Uji Fasilitas',
            'address'           => 'Jl. Uji Fasilitas No. 1',
            'lat'               => -6.990,
            'lng'               => 107.620,
            'capacity'          => 100,
            'occupancy'         => 10,
            'contact'           => '08123456',
            'status'            => 'aktif',
        ]);

        $facGood = ShelterFacility::create([
            'shelter_id' => $shelter->id,
            'type'       => 'mck',
            'units'      => 4,
            'capacity'   => 80,
            'condition'  => 'baik',
            'note'       => 'Bersih dan lancar',
        ]);

        $facBroken = ShelterFacility::create([
            'shelter_id' => $shelter->id,
            'type'       => 'listrik',
            'units'      => 1,
            'capacity'   => 100,
            'condition'  => 'tidak_berfungsi',
            'note'       => 'Genset rusak total',
        ]);

        $this->assertEquals('MCK / Sanitasi', $facGood->type_label);
        $this->assertEquals('Baik', $facGood->condition_label);
        $this->assertStringContainsString('bg-emerald-50', $facGood->condition_badge_classes);

        $this->assertEquals('Listrik & Penerangan', $facBroken->type_label);
        $this->assertEquals('Tidak Berfungsi', $facBroken->condition_label);
        $this->assertStringContainsString('bg-red-50', $facBroken->condition_badge_classes);

        $this->assertEquals(1, $shelter->broken_facilities_count);
        $this->assertEquals(1, $shelter->needs_attention_facilities_count);
        $this->assertEquals(1, $shelter->facilities()->broken()->count());
    }

    public function test_coordinator_can_open_shelter_detail_and_view_facilities_tab(): void
    {
        $budi = User::where('email', 'koordinator.budi@resqhub.id')->first();
        $shelter = Shelter::where('coordinator_id', $budi->id)->first();

        Livewire::actingAs($budi)
            ->test(ShelterManagement::class)
            ->call('openDetailModal', $shelter->id, 'facilities')
            ->assertSet('showDetailModal', true)
            ->assertSet('detailShelterId', $shelter->id)
            ->assertSet('detailActiveTab', 'facilities')
            ->assertSee('Fasilitas Posko')
            ->assertSee('MCK / Sanitasi')
            ->assertSee('Dapur Umum')
            ->assertSee('Pos Kesehatan')
            ->call('setDetailTab', 'info')
            ->assertSet('detailActiveTab', 'info')
            ->assertSee($shelter->address);
    }

    public function test_coordinator_cannot_open_detail_modal_of_other_coordinators_shelter(): void
    {
        $budi = User::where('email', 'koordinator.budi@resqhub.id')->first();
        $siti = User::where('email', 'koordinator.siti@resqhub.id')->first();
        $budiShelter = Shelter::where('coordinator_id', $budi->id)->first();

        Livewire::actingAs($siti)
            ->test(ShelterManagement::class)
            ->call('openDetailModal', $budiShelter->id, 'facilities')
            ->assertStatus(404);
    }

    public function test_public_shelter_search_displays_facility_summary_badges(): void
    {
        Livewire::test(ShelterSearch::class)
            ->assertSee('Fasilitas Tersedia')
            ->assertSee('MCK / Sanitasi')
            ->assertSee('Dapur Umum');
    }
}
