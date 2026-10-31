<?php

namespace Tests\Feature;

use App\Livewire\Admin\RefugeeManagement;
use App\Models\Refugee;
use App\Models\Shelter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RefugeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_koordinator_can_check_in_refugee_and_occupancy_increases_automatically(): void
    {
        $koordinator = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();
        $shelter = Shelter::findOrFail(1);
        $initialOccupancy = $shelter->occupancy;

        $response = $this->actingAs($koordinator)->postJson(route('admin.refugees.store'), [
            'shelter_id' => $shelter->id,
            'head_name' => 'Dedi Kusnandar',
            'nik' => '3204119988770001',
            'members' => 4,
            'infants' => 1,
            'elderly' => 1,
            'pregnant' => 1,
            'disabled' => 0,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.head_name', 'Dedi Kusnandar')
            ->assertJsonPath('data.status', 'di_posko')
            ->assertJsonPath('data.masked_nik', '3204********0001')
            ->assertJsonPath('warnings', []);

        $this->assertEquals($initialOccupancy + 4, $shelter->fresh()->occupancy);

        $refugeeId = $response->json('data.id');
        $this->assertDatabaseHas('refugee_movements', [
            'refugee_id' => $refugeeId,
            'from_shelter_id' => null,
            'to_shelter_id' => $shelter->id,
            'type' => 'masuk',
            'created_by' => $koordinator->id,
        ]);
    }

    public function test_check_in_generates_warnings_when_exceeding_capacity_or_nik_active_in_another_shelter(): void
    {
        $koordinatorSiti = User::where('email', 'koordinator.siti@resqhub.id')->firstOrFail();
        $shelter2 = Shelter::findOrFail(2); // capacity: 150, occupancy: 142

        // Gunakan NIK yang masih aktif di Posko 1 (Ahmad Fauzi: 3204111205800001)
        // dan jumlah anggota 10 jiwa sehingga 142 + 10 = 152 (> 150)
        $response = $this->actingAs($koordinatorSiti)->postJson(route('admin.refugees.store'), [
            'shelter_id' => $shelter2->id,
            'head_name' => 'Keluarga Darurat Banjir',
            'nik' => '3204111205800001',
            'members' => 10,
            'infants' => 2,
            'elderly' => 1,
            'pregnant' => 0,
            'disabled' => 0,
        ]);

        $response->assertCreated();
        $warnings = $response->json('warnings');
        $warningTypes = array_column($warnings, 'type');

        $this->assertContains('capacity_exceeded', $warningTypes);
        $this->assertContains('duplicate_active_nik', $warningTypes);
        $this->assertEquals(152, $shelter2->fresh()->occupancy);
        $this->assertEquals('penuh', $shelter2->fresh()->status);
    }

    public function test_list_and_detail_refugees_support_search_status_filter_masked_nik_and_movement_history(): void
    {
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();

        // 1. Filter pencarian nama kepala keluarga
        $listResponse = $this->actingAs($koordinatorBudi)->getJson(route('admin.refugees.index', [
            'shelter_id' => 1,
            'search' => 'Ahmad Fauzi',
            'status' => 'di_posko',
        ]));

        $listResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.head_name', 'Ahmad Fauzi')
            ->assertJsonPath('data.0.masked_nik', '3204********0001');

        // 2. Lihat detail & riwayat pergerakan
        $detailResponse = $this->actingAs($koordinatorBudi)->getJson(route('admin.refugees.show', 1));

        $detailResponse->assertOk()
            ->assertJsonPath('masked_nik', '3204********0001')
            ->assertJsonPath('data.movements.0.type', 'masuk');
    }

    public function test_koordinator_can_update_family_data_and_occupancy_adjusts_automatically(): void
    {
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();
        $shelter1 = Shelter::findOrFail(1);
        $refugee = Refugee::findOrFail(1); // members awal = 4, status = di_posko
        $initialOccupancy = $shelter1->occupancy; // 185

        $response = $this->actingAs($koordinatorBudi)->putJson(route('admin.refugees.update', $refugee->id), [
            'head_name' => 'Ahmad Fauzi Updated',
            'nik' => '3204111205800001',
            'members' => 6, // bertambah 2 jiwa
            'infants' => 2,
            'elderly' => 1,
            'pregnant' => 0,
            'disabled' => 1,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.head_name', 'Ahmad Fauzi Updated')
            ->assertJsonPath('data.members', 6);

        $this->assertEquals($initialOccupancy + 2, $shelter1->fresh()->occupancy);
    }

    public function test_koordinator_can_mark_refugee_pulang_and_occupancy_decreases_with_history(): void
    {
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();
        $shelter1 = Shelter::findOrFail(1);
        $refugee = Refugee::findOrFail(1); // members = 4
        $initialOccupancy = $shelter1->occupancy;

        $response = $this->actingAs($koordinatorBudi)->postJson(route('admin.refugees.checkout', $refugee->id));

        $response->assertOk()
            ->assertJsonPath('data.status', 'pulang');

        $this->assertNotNull($refugee->fresh()->checked_out_at);
        $this->assertEquals($initialOccupancy - 4, $shelter1->fresh()->occupancy);

        $this->assertDatabaseHas('refugee_movements', [
            'refugee_id' => $refugee->id,
            'from_shelter_id' => $shelter1->id,
            'to_shelter_id' => null,
            'type' => 'pulang',
            'created_by' => $koordinatorBudi->id,
        ]);
    }

    public function test_koordinator_can_transfer_refugee_when_destination_shelter_has_capacity(): void
    {
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();
        $shelter1 = Shelter::findOrFail(1); // occupancy: 185
        $shelter2 = Shelter::findOrFail(2); // capacity: 150, occupancy: 142 (tersedia 8)
        $refugee = Refugee::findOrFail(1);  // members: 4

        $response = $this->actingAs($koordinatorBudi)->postJson(route('admin.refugees.transfer', $refugee->id), [
            'to_shelter_id' => $shelter2->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'pindah')
            ->assertJsonPath('data.shelter_id', $shelter2->id);

        $this->assertEquals(185 - 4, $shelter1->fresh()->occupancy);
        $this->assertEquals(142 + 4, $shelter2->fresh()->occupancy);

        $this->assertDatabaseHas('refugee_movements', [
            'refugee_id' => $refugee->id,
            'from_shelter_id' => $shelter1->id,
            'to_shelter_id' => $shelter2->id,
            'type' => 'pindah',
            'created_by' => $koordinatorBudi->id,
        ]);
    }

    public function test_transfer_is_rejected_when_destination_shelter_does_not_have_enough_capacity(): void
    {
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();
        $shelter1 = Shelter::findOrFail(1);
        $shelter2 = Shelter::findOrFail(2); // capacity: 150, occupancy: 142 (tersedia 8)

        // Buat pengungsi dengan 9 anggota keluarga di Posko 1
        $refugee = Refugee::create([
            'shelter_id' => $shelter1->id,
            'head_name' => 'Keluarga Besar Hasan',
            'nik' => '3204115566770009',
            'members' => 9,
            'infants' => 0,
            'elderly' => 0,
            'pregnant' => 0,
            'disabled' => 0,
            'status' => 'di_posko',
            'checked_in_at' => now(),
        ]);
        $shelter1->adjustOccupancy(9);

        $response = $this->actingAs($koordinatorBudi)->postJson(route('admin.refugees.transfer', $refugee->id), [
            'to_shelter_id' => $shelter2->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['to_shelter_id']);

        // Okupansi posko tujuan tidak berubah
        $this->assertEquals(142, $shelter2->fresh()->occupancy);
    }

    public function test_delete_refugee_blocked_if_has_health_checks_or_distributions_and_allowed_otherwise(): void
    {
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();
        $shelter1 = Shelter::findOrFail(1);

        // 1. Refugee ID 1 sudah memiliki pemeriksaan kesehatan & penyaluran bantuan di seeder -> gagal dihapus
        $blockedResponse = $this->actingAs($koordinatorBudi)->deleteJson(route('admin.refugees.destroy', 1), [
            'delete_reason' => 'Coba hapus data yang sudah ada rekam medis',
        ]);

        $blockedResponse->assertStatus(422)
            ->assertJsonValidationErrors(['delete']);
        $this->assertNull(Refugee::withTrashed()->find(1)->deleted_at);

        // 2. Pengungsi baru (salah input) tanpa catatan medis/penyaluran -> berhasil soft delete & okupansi dikoreksi
        $checkInResponse = $this->actingAs($koordinatorBudi)->postJson(route('admin.refugees.store'), [
            'shelter_id' => $shelter1->id,
            'head_name' => 'Salah Input Keluarga',
            'nik' => null,
            'members' => 3,
            'infants' => 0,
            'elderly' => 0,
            'pregnant' => 0,
            'disabled' => 0,
        ]);

        $newRefugeeId = $checkInResponse->json('data.id');
        $occupancyAfterCheckIn = $shelter1->fresh()->occupancy;

        $deleteResponse = $this->actingAs($koordinatorBudi)->deleteJson(route('admin.refugees.destroy', $newRefugeeId), [
            'delete_reason' => 'Salah input duplikasi kepala keluarga',
        ]);

        $deleteResponse->assertOk();

        $trashed = Refugee::withTrashed()->findOrFail($newRefugeeId);
        $this->assertNotNull($trashed->deleted_at);
        $this->assertEquals('Salah input duplikasi kepala keluarga', $trashed->delete_reason);
        $this->assertEquals($occupancyAfterCheckIn - 3, $shelter1->fresh()->occupancy);
    }

    public function test_koordinator_cannot_manage_unassigned_shelter_and_unauthorized_role_blocked(): void
    {
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();
        $warga = User::where('role', 'warga')->firstOrFail();

        // Budi tidak mengelola Posko 2 (milik Siti)
        $forbiddenCheckIn = $this->actingAs($koordinatorBudi)->postJson(route('admin.refugees.store'), [
            'shelter_id' => 2,
            'head_name' => 'Tes Akses Silang',
            'members' => 2,
        ]);
        $forbiddenCheckIn->assertForbidden();

        // Warga tidak boleh mengakses halaman manajemen pengungsi
        $wargaResponse = $this->actingAs($warga)->get(route('admin.refugees'));
        $wargaResponse->assertForbidden();
    }

    public function test_livewire_refugee_management_component_works_end_to_end(): void
    {
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();
        $shelter1 = Shelter::findOrFail(1);
        $shelter2 = Shelter::findOrFail(2);
        $initialOccupancy1 = $shelter1->occupancy;
        $initialOccupancy2 = $shelter2->occupancy;

        // 1. Check-in pengungsi baru via panel Livewire
        Livewire::actingAs($koordinatorBudi)
            ->test(RefugeeManagement::class)
            ->assertStatus(200)
            ->assertSee('Data Pengungsi')
            ->assertSee('Ahmad Fauzi')
            ->assertSee('3204********0001')
            ->set('shelter_id', $shelter1->id)
            ->set('head_name', 'Rahmat Hidayat')
            ->set('nik', '3204114455660007')
            ->set('members', 3)
            ->set('infants', 1)
            ->call('checkIn')
            ->assertHasNoErrors();

        $this->assertEquals($initialOccupancy1 + 3, $shelter1->fresh()->occupancy);

        $newRefugee = Refugee::where('head_name', 'Rahmat Hidayat')->firstOrFail();

        // 2. Ubah data & pilih Perubahan Status = Pindah Posko via panel kanan
        Livewire::actingAs($koordinatorBudi)
            ->test(RefugeeManagement::class)
            ->call('openEditModal', $newRefugee->id)
            ->assertSet('panelMode', 'edit')
            ->assertSee('Ubah data: Rahmat Hidayat')
            ->set('members', 4)
            ->set('statusAction', 'pindah')
            ->set('to_shelter_id', $shelter2->id)
            ->call('updateRefugee')
            ->assertHasNoErrors();

        $this->assertEquals('pindah', $newRefugee->fresh()->status);
        $this->assertEquals($shelter2->id, $newRefugee->fresh()->shelter_id);
        $this->assertEquals($initialOccupancy1, $shelter1->fresh()->occupancy);
        $this->assertEquals($initialOccupancy2 + 4, $shelter2->fresh()->occupancy);

        // 3. Pastikan status kontekstual (Opsi B):
        //    - Di posko lama (Posko 1): tampil sebagai riwayat berstatus 'pindah' (tidak muncul saat filter 'di_posko')
        //    - Di posko baru (Posko 2): tampil sebagai pengungsi aktif berstatus 'di_posko'
        $this->assertEquals('pindah', $newRefugee->fresh()->effectiveStatusForShelter($shelter1->id));
        $this->assertEquals('di_posko', $newRefugee->fresh()->effectiveStatusForShelter($shelter2->id));

        $admin = User::where('email', 'admin@resqhub.id')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(RefugeeManagement::class)
            ->set('shelterFilter', (string) $shelter1->id)
            ->set('statusFilter', 'di_posko')
            ->assertDontSee('Rahmat Hidayat')
            ->set('statusFilter', 'pindah')
            ->assertSee('Rahmat Hidayat')
            ->assertSee('Pindah ke ' . $shelter2->name)
            ->set('shelterFilter', (string) $shelter2->id)
            ->set('statusFilter', 'di_posko')
            ->assertSee('Rahmat Hidayat');
    }

    public function test_reconcile_transferred_refugees_moves_legacy_seeded_refugee_to_destination_shelter(): void
    {
        $admin = User::where('email', 'admin@resqhub.id')->firstOrFail();

        // Simulasikan kondisi data lama di mana shelter_id tertinggal di posko asal (2)
        // padahal riwayat perpindahan terakhir menuju posko tujuan (1)
        Refugee::where('id', 3)->update(['shelter_id' => 2, 'status' => 'pindah']);

        Livewire::actingAs($admin)
            ->test(RefugeeManagement::class)
            ->set('shelterFilter', '2')
            ->set('statusFilter', 'di_posko')
            ->assertDontSee('Ujang Suherman')
            ->set('statusFilter', 'pindah')
            ->assertSee('Ujang Suherman')
            ->set('shelterFilter', '1')
            ->set('statusFilter', 'di_posko')
            ->assertSee('Ujang Suherman');

        $refugee3 = Refugee::findOrFail(3);
        $this->assertEquals(1, $refugee3->shelter_id);
        $this->assertEquals('pindah', $refugee3->effectiveStatusForShelter(2));
        $this->assertEquals('di_posko', $refugee3->effectiveStatusForShelter(1));
    }
}

