<?php

namespace Tests\Feature;

use App\Livewire\Admin\ShelterManagement;
use App\Livewire\Public\ShelterSearch;
use App\Models\DisasterEvent;
use App\Models\Shelter;
use App\Models\ShelterFacility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_coordinator_can_add_new_facility_to_their_shelter(): void
    {
        $siti = User::where('email', 'koordinator.siti@resqhub.id')->first();
        $shelter = Shelter::where('coordinator_id', $siti->id)->first();

        // Pastikan 'laktasi' belum ada pada posko Siti
        $this->assertDatabaseMissing('shelter_facilities', [
            'shelter_id' => $shelter->id,
            'type'       => 'laktasi',
        ]);

        Livewire::actingAs($siti)
            ->test(ShelterManagement::class)
            ->call('openDetailModal', $shelter->id, 'facilities')
            ->call('openFacilityCreateForm')
            ->assertSet('facilityFormMode', 'create')
            ->set('facility_type', 'laktasi')
            ->set('facility_units', 2)
            ->set('facility_capacity', 12)
            ->set('facility_condition', 'baik')
            ->set('facility_note', 'Ruang tertutup dengan tirai bersih')
            ->call('saveFacility')
            ->assertHasNoErrors()
            ->assertSet('facilityFormMode', '');

        $this->assertDatabaseHas('shelter_facilities', [
            'shelter_id' => $shelter->id,
            'type'       => 'laktasi',
            'units'      => 2,
            'capacity'   => 12,
            'condition'  => 'baik',
            'note'       => 'Ruang tertutup dengan tirai bersih',
        ]);
    }

    public function test_cannot_add_duplicate_facility_type_in_the_same_shelter(): void
    {
        $siti = User::where('email', 'koordinator.siti@resqhub.id')->first();
        $shelter = Shelter::where('coordinator_id', $siti->id)->first();

        // Pastikan 'mck' sudah terdaftar di posko Siti via seeder
        $this->assertTrue($shelter->facilities()->where('type', 'mck')->exists());

        Livewire::actingAs($siti)
            ->test(ShelterManagement::class)
            ->call('openDetailModal', $shelter->id, 'facilities')
            ->call('openFacilityCreateForm')
            ->set('facility_type', 'mck')
            ->set('facility_units', 3)
            ->set('facility_capacity', 40)
            ->set('facility_condition', 'baik')
            ->call('saveFacility')
            ->assertHasErrors(['facility_type'])
            ->assertSet('facilityFormMode', 'create');

        $this->assertEquals(1, $shelter->facilities()->where('type', 'mck')->count());
    }

    public function test_different_shelters_can_have_the_same_facility_type(): void
    {
        $event = DisasterEvent::where('status', 'aktif')->first();
        $siti = User::where('email', 'koordinator.siti@resqhub.id')->first();

        $newShelter = Shelter::create([
            'disaster_event_id' => $event->id,
            'coordinator_id'    => $siti->id,
            'name'              => 'Posko Cabang Dayeuhkolot',
            'address'           => 'Jl. Cabang No. 9',
            'lat'               => -6.985,
            'lng'               => 107.621,
            'capacity'          => 80,
            'occupancy'         => 0,
            'contact'           => '08199',
            'status'            => 'aktif',
        ]);

        // Posko Siti pertama sudah punya 'mck', posko kedua boleh punya 'mck' juga
        Livewire::actingAs($siti)
            ->test(ShelterManagement::class)
            ->call('openDetailModal', $newShelter->id, 'facilities')
            ->call('openFacilityCreateForm')
            ->set('facility_type', 'mck')
            ->set('facility_units', 2)
            ->set('facility_capacity', 30)
            ->set('facility_condition', 'baik')
            ->call('saveFacility')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('shelter_facilities', [
            'shelter_id' => $newShelter->id,
            'type'       => 'mck',
        ]);
    }

    public function test_coordinator_can_update_facility_condition_units_capacity_and_note(): void
    {
        $siti = User::where('email', 'koordinator.siti@resqhub.id')->first();
        $shelter = Shelter::where('coordinator_id', $siti->id)->first();
        $facility = $shelter->facilities()->where('type', 'mck')->first();

        Livewire::actingAs($siti)
            ->test(ShelterManagement::class)
            ->call('openDetailModal', $shelter->id, 'facilities')
            ->call('openFacilityEditForm', $facility->id)
            ->assertSet('facilityFormMode', 'edit')
            ->set('facility_units', 5)
            ->set('facility_capacity', 100)
            ->set('facility_condition', 'tidak_berfungsi')
            ->set('facility_note', 'Saluran air utama pecah, butuh perbaikan segera')
            ->call('saveFacility')
            ->assertHasNoErrors()
            ->assertSet('facilityFormMode', '');

        $facility->refresh();
        $this->assertEquals(5, $facility->units);
        $this->assertEquals(100, $facility->capacity);
        $this->assertEquals('tidak_berfungsi', $facility->condition);
        $this->assertEquals('Saluran air utama pecah, butuh perbaikan segera', $facility->note);
    }

    public function test_coordinator_can_upload_and_replace_optional_facility_photo(): void
    {
        Storage::fake('public');

        $siti = User::where('email', 'koordinator.siti@resqhub.id')->first();
        $shelter = Shelter::where('coordinator_id', $siti->id)->first();

        $firstPhoto = UploadedFile::fake()->image('listrik_awal.jpg', 640, 480);

        Livewire::actingAs($siti)
            ->test(ShelterManagement::class)
            ->call('openDetailModal', $shelter->id, 'facilities')
            ->call('openFacilityCreateForm')
            ->set('facility_type', 'listrik')
            ->set('facility_units', 2)
            ->set('facility_capacity', 150)
            ->set('facility_condition', 'rusak_ringan')
            ->set('facility_note', 'Kabel panel agak longgar')
            ->set('facility_photo', $firstPhoto)
            ->call('saveFacility')
            ->assertHasNoErrors();

        $facility = ShelterFacility::where('shelter_id', $shelter->id)
            ->where('type', 'listrik')
            ->firstOrFail();

        $this->assertNotNull($facility->photo_path);
        Storage::disk('public')->assertExists($facility->photo_path);
        $oldPhotoPath = $facility->photo_path;

        // Ganti foto saat edit kondisi
        $replacementPhoto = UploadedFile::fake()->image('listrik_perbaikan.png', 800, 600);

        Livewire::actingAs($siti)
            ->test(ShelterManagement::class)
            ->call('openDetailModal', $shelter->id, 'facilities')
            ->call('openFacilityEditForm', $facility->id)
            ->set('facility_condition', 'baik')
            ->set('facility_note', 'Kabel panel sudah diperbaiki')
            ->set('facility_photo', $replacementPhoto)
            ->call('saveFacility')
            ->assertHasNoErrors();

        $facility->refresh();
        $this->assertEquals('baik', $facility->condition);
        $this->assertNotEquals($oldPhotoPath, $facility->photo_path);
        Storage::disk('public')->assertMissing($oldPhotoPath);
        Storage::disk('public')->assertExists($facility->photo_path);
    }

    public function test_invalid_facility_photo_upload_fails_validation(): void
    {
        Storage::fake('public');

        $siti = User::where('email', 'koordinator.siti@resqhub.id')->first();
        $shelter = Shelter::where('coordinator_id', $siti->id)->first();

        $invalidFile = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        Livewire::actingAs($siti)
            ->test(ShelterManagement::class)
            ->call('openDetailModal', $shelter->id, 'facilities')
            ->call('openFacilityCreateForm')
            ->set('facility_type', 'ramah_anak')
            ->set('facility_units', 1)
            ->set('facility_capacity', 25)
            ->set('facility_condition', 'baik')
            ->set('facility_photo', $invalidFile)
            ->call('saveFacility')
            ->assertHasErrors(['facility_photo']);
    }

    public function test_coordinator_cannot_read_other_shelter_by_tampering_detail_state(): void
    {
        $siti = User::where('email', 'koordinator.siti@resqhub.id')->first();
        $budi = User::where('email', 'koordinator.budi@resqhub.id')->first();
        $budiShelter = Shelter::where('coordinator_id', $budi->id)->first();

        Livewire::actingAs($siti)
            ->test(ShelterManagement::class)
            ->set('detailShelterId', $budiShelter->id)
            ->set('showDetailModal', true)
            ->assertDontSee('4 bilik pria, 4 bilik wanita');
    }

    public function test_removing_existing_photo_deletes_the_file(): void
    {
        Storage::fake('public');

        $siti = User::where('email', 'koordinator.siti@resqhub.id')->first();
        $shelter = Shelter::where('coordinator_id', $siti->id)->first();
        $facility = $shelter->facilities()->where('type', 'mck')->first();
        $path = UploadedFile::fake()->image('mck.jpg')->store('facilities', 'public');
        $facility->update(['photo_path' => $path]);

        Livewire::actingAs($siti)
            ->test(ShelterManagement::class)
            ->call('openDetailModal', $shelter->id, 'facilities')
            ->call('openFacilityEditForm', $facility->id)
            ->call('markRemoveExistingFacilityPhoto')
            ->call('saveFacility')
            ->assertHasNoErrors();

        $this->assertNull($facility->fresh()->photo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_deleting_a_shelter_also_removes_its_facility_photos(): void
    {
        Storage::fake('public');

        $siti = User::where('email', 'koordinator.siti@resqhub.id')->first();
        $shelter = Shelter::create([
            'disaster_event_id' => DisasterEvent::where('status', 'aktif')->first()->id,
            'coordinator_id'    => $siti->id,
            'name'              => 'Posko Salah Input',
            'address'           => 'Jl. Salah Input',
            'lat'               => -6.98,
            'lng'               => 107.62,
            'capacity'          => 50,
            'occupancy'         => 0,
            'contact'           => '08123456789',
            'status'            => 'aktif',
        ]);
        $path = UploadedFile::fake()->image('dapur.jpg')->store('facilities', 'public');
        ShelterFacility::create([
            'shelter_id' => $shelter->id,
            'type'       => 'dapur_umum',
            'units'      => 1,
            'capacity'   => 10,
            'condition'  => 'baik',
            'photo_path' => $path,
        ]);

        Livewire::actingAs($siti)
            ->test(ShelterManagement::class)
            ->call('confirmDelete', $shelter->id)
            ->call('destroy');

        $this->assertDatabaseMissing('shelters', ['id' => $shelter->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_unauthorized_coordinator_cannot_save_facility_for_other_shelter(): void
    {
        $budi = User::where('email', 'koordinator.budi@resqhub.id')->first();
        $siti = User::where('email', 'koordinator.siti@resqhub.id')->first();
        $budiShelter = Shelter::where('coordinator_id', $budi->id)->first();

        Livewire::actingAs($siti)
            ->test(ShelterManagement::class)
            ->set('detailShelterId', $budiShelter->id)
            ->set('facilityFormMode', 'create')
            ->set('facility_type', 'mck')
            ->set('facility_units', 1)
            ->set('facility_capacity', 10)
            ->set('facility_condition', 'baik')
            ->call('saveFacility')
            ->assertStatus(404);
    }
}
