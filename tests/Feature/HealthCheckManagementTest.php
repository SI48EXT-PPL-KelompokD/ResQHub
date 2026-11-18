<?php

namespace Tests\Feature;

use App\Livewire\Admin\HealthCheckManagement;
use App\Models\ActivityLog;
use App\Models\HealthCheck;
use App\Models\Refugee;
use App\Models\User;
use App\Notifications\CriticalTriageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class HealthCheckManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_nakes_can_create_health_check_and_merah_triage_notifies_shelter_coordinator(): void
    {
        Notification::fake();

        $nakes = User::where('email', 'nakes.hendra@resqhub.id')->firstOrFail();
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();
        $refugee = Refugee::findOrFail(1); // Di Posko 1 (Koordinator: Budi)

        // 1. Triase Hijau -> tersimpan tanpa memicu notifikasi darurat
        $greenResponse = $this->actingAs($nakes)->postJson(route('admin.health-checks.store'), [
            'refugee_id' => $refugee->id,
            'complaint' => 'Batuk ringan dan pilek sejak pagi.',
            'temperature' => 36.9,
            'blood_pressure' => '120/80',
            'pulse' => 82,
            'triage' => 'hijau',
            'action' => 'Pemberian vitamin C dan obat batuk sirup.',
        ]);

        $greenResponse->assertCreated()
            ->assertJsonPath('data.triage', 'hijau')
            ->assertJsonPath('data.triage_label', 'Hijau (ringan)');

        Notification::assertNothingSent();

        // 2. Triase Merah -> tersimpan & memicu notifikasi ke koordinator posko terkait
        $redResponse = $this->actingAs($nakes)->postJson(route('admin.health-checks.store'), [
            'refugee_id' => $refugee->id,
            'complaint' => 'Nyeri dada hebat menjalar ke lengan kiri dan keringat dingin.',
            'temperature' => 37.8,
            'blood_pressure' => '170/105',
            'pulse' => 118,
            'triage' => 'merah',
            'action' => 'Pasang oksigen, observasi EKG, siapkan rujukan segera.',
        ]);

        $redResponse->assertCreated()
            ->assertJsonPath('data.triage', 'merah')
            ->assertJsonPath('data.triage_label', 'Merah (gawat)');

        Notification::assertSentTo(
            $koordinatorBudi,
            CriticalTriageNotification::class,
            function (CriticalTriageNotification $notification) use ($koordinatorBudi, $refugee) {
                $payload = $notification->toArray($koordinatorBudi);

                return $payload['refugee_id'] === $refugee->id
                    && $payload['triage'] === 'merah'
                    && $payload['shelter_id'] === $refugee->shelter_id;
            }
        );

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $nakes->id,
            'subject_type' => HealthCheck::class,
            'subject_id' => $redResponse->json('data.id'),
            'action' => 'created_health_check',
        ]);
    }

    public function test_create_health_check_rejects_refugee_who_already_checked_out(): void
    {
        $nakes = User::where('email', 'nakes.hendra@resqhub.id')->firstOrFail();
        $returnedRefugee = Refugee::findOrFail(4); // status = 'pulang'

        $response = $this->actingAs($nakes)->postJson(route('admin.health-checks.store'), [
            'refugee_id' => $returnedRefugee->id,
            'complaint' => 'Pusing ringan',
            'temperature' => 36.6,
            'blood_pressure' => '120/80',
            'pulse' => 78,
            'triage' => 'hijau',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['refugee_id']);
    }

    public function test_history_and_daily_triage_summary_are_accessible_only_to_nakes_and_related_coordinator(): void
    {
        // Pastikan seluruh 3 data seeder berada pada tanggal hari ini (tidak bergantung jam eksekusi test)
        HealthCheck::query()->update(['checked_at' => now()]);

        $nakes = User::where('email', 'nakes.hendra@resqhub.id')->firstOrFail();
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();
        $koordinatorSiti = User::where('email', 'koordinator.siti@resqhub.id')->firstOrFail();
        $warga = User::where('role', 'warga')->firstOrFail();
        $relawan = User::where('role', 'relawan')->firstOrFail();

        // 1. Nakes dapat melihat riwayat pemeriksaan pengungsi & ringkasan triase harian seluruh posko
        $historyResponse = $this->actingAs($nakes)->getJson(route('admin.health-checks.history', 1));
        $historyResponse->assertOk()
            ->assertJsonPath('data.id', 1)
            ->assertJsonCount(2, 'health_checks');

        $summaryResponse = $this->actingAs($nakes)->getJson(route('admin.health-checks.summary'));
        $summaryResponse->assertOk()
            ->assertJsonPath('data.totals.merah', 1)
            ->assertJsonPath('data.totals.kuning', 1)
            ->assertJsonPath('data.totals.hijau', 1)
            ->assertJsonPath('data.totals.total', 3);

        // 2. Koordinator Budi dapat mengakses data Posko 1 (kelolaannya), namun ditolak pada Posko 2 (milik Siti)
        $this->actingAs($koordinatorBudi)
            ->getJson(route('admin.health-checks.history', 1))
            ->assertOk();

        $this->actingAs($koordinatorBudi)
            ->getJson(route('admin.health-checks.summary', ['shelter_id' => 1]))
            ->assertOk()
            ->assertJsonPath('data.totals.total', 3);

        $this->actingAs($koordinatorBudi)
            ->getJson(route('admin.health-checks.summary', ['shelter_id' => 2]))
            ->assertForbidden();

        // 3. Koordinator Siti (Posko 2) ditolak mengakses riwayat pengungsi di Posko 1
        $this->actingAs($koordinatorSiti)
            ->getJson(route('admin.health-checks.history', 1))
            ->assertForbidden();

        // 4. Role selain nakes/koordinator (warga, relawan) ditolak mengakses data pemeriksaan
        $this->actingAs($warga)
            ->getJson(route('admin.health-checks.index'))
            ->assertForbidden();

        $this->actingAs($relawan)
            ->getJson(route('admin.health-checks.summary'))
            ->assertForbidden();

        // 5. Koordinator hanya memiliki akses lihat (tidak dapat menambah pemeriksaan)
        $this->actingAs($koordinatorBudi)
            ->postJson(route('admin.health-checks.store'), [
                'refugee_id' => 1,
                'complaint' => 'Coba input oleh koordinator',
                'temperature' => 37.0,
                'blood_pressure' => '120/80',
                'pulse' => 80,
                'triage' => 'hijau',
            ])
            ->assertForbidden();
    }

    public function test_creator_nakes_can_update_within_24_hours_and_changes_are_logged(): void
    {
        $nakesDewi = User::where('email', 'nakes.dewi@resqhub.id')->firstOrFail();
        $nakesHendra = User::where('email', 'nakes.hendra@resqhub.id')->firstOrFail();

        // HealthCheck ID 2 dibuat oleh Ns. Dewi (id=5) 12 jam yang lalu (masih < 24 jam)
        $check = HealthCheck::findOrFail(2);

        // 1. Nakes lain (dr. Hendra) tidak boleh mengoreksi catatan milik Ns. Dewi
        $this->actingAs($nakesHendra)
            ->putJson(route('admin.health-checks.update', $check->id), [
                'complaint' => 'Coba ubah oleh nakes lain',
                'temperature' => 37.2,
                'blood_pressure' => '130/85',
                'pulse' => 88,
                'triage' => 'kuning',
            ])
            ->assertForbidden();

        // 2. Nakes pembuat (Ns. Dewi) berhasil mengoreksi catatan dalam batas <= 24 jam
        $updateResponse = $this->actingAs($nakesDewi)
            ->putJson(route('admin.health-checks.update', $check->id), [
                'complaint' => 'Ibu hamil 32 minggu pusing berkurang setelah istirahat.',
                'temperature' => 37.4,
                'blood_pressure' => '135/88',
                'pulse' => 86,
                'triage' => 'kuning',
                'action' => 'Lanjutkan observasi tekanan darah per 4 jam.',
            ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.temperature', '37.4')
            ->assertJsonPath('data.blood_pressure', '135/88');

        $log = ActivityLog::where('subject_type', HealthCheck::class)
            ->where('subject_id', $check->id)
            ->where('action', 'updated_health_check')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($nakesDewi->id, $log->user_id);
        $this->assertEquals('36.8', $log->changes['temperature']['old']);
        $this->assertEquals('37.4', $log->changes['temperature']['new']);
        $this->assertEquals('130/85', $log->changes['blood_pressure']['old']);
        $this->assertEquals('135/88', $log->changes['blood_pressure']['new']);
    }

    public function test_update_is_rejected_after_24_hours_window_expired(): void
    {
        $nakesDewi = User::where('email', 'nakes.dewi@resqhub.id')->firstOrFail();
        $check = HealthCheck::findOrFail(3); // dibuat oleh Ns. Dewi

        // Simulasikan catatan dibuat 25 jam yang lalu
        $check->forceFill([
            'created_at' => now()->subHours(25),
        ])->save();

        $response = $this->actingAs($nakesDewi)
            ->putJson(route('admin.health-checks.update', $check->id), [
                'complaint' => 'Koreksi terlambat setelah lewat 24 jam',
                'temperature' => 36.8,
                'blood_pressure' => '110/70',
                'pulse' => 90,
                'triage' => 'hijau',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['health_check']);
    }

    public function test_creator_nakes_can_soft_delete_with_reason_only_when_no_medical_referral_exists(): void
    {
        $nakesHendra = User::where('email', 'nakes.hendra@resqhub.id')->firstOrFail();
        $nakesDewi = User::where('email', 'nakes.dewi@resqhub.id')->firstOrFail();

        // 1. HealthCheck ID 1 dibuat oleh dr. Hendra tetapi sudah memiliki rujukan medis -> ditolak (422)
        $blockedResponse = $this->actingAs($nakesHendra)
            ->deleteJson(route('admin.health-checks.destroy', 1), [
                'delete_reason' => 'Coba hapus catatan yang sudah ada rujukan',
            ]);

        $blockedResponse->assertStatus(422)
            ->assertJsonValidationErrors(['delete']);
        $this->assertNull(HealthCheck::withTrashed()->findOrFail(1)->deleted_at);

        // 2. Nakes lain (dr. Hendra) tidak dapat menghapus catatan milik Ns. Dewi (ID 3) -> 403
        $this->actingAs($nakesHendra)
            ->deleteJson(route('admin.health-checks.destroy', 3), [
                'delete_reason' => 'Coba hapus catatan nakes lain',
            ])
            ->assertForbidden();

        // 3. Nakes pembuat (Ns. Dewi) berhasil soft delete catatan tanpa rujukan medis (ID 3) dengan alasan
        $deleteResponse = $this->actingAs($nakesDewi)
            ->deleteJson(route('admin.health-checks.destroy', 3), [
                'delete_reason' => 'Salah input tanda vital pada pasien balita',
            ]);

        $deleteResponse->assertOk();

        $trashed = HealthCheck::withTrashed()->findOrFail(3);
        $this->assertNotNull($trashed->deleted_at);
        $this->assertEquals('Salah input tanda vital pada pasien balita', $trashed->delete_reason);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $nakesDewi->id,
            'subject_type' => HealthCheck::class,
            'subject_id' => 3,
            'action' => 'deleted_health_check',
        ]);
    }

    public function test_livewire_health_check_management_component_works_end_to_end(): void
    {
        Notification::fake();

        $nakesDewi = User::where('email', 'nakes.dewi@resqhub.id')->firstOrFail();
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();

        // 1. Nakes mencari & memilih posko lalu mencari & memilih pengungsi, kemudian menyimpan pemeriksaan baru
        Livewire::actingAs($nakesDewi)
            ->test(HealthCheckManagement::class)
            ->assertStatus(200)
            ->assertSee('Pemeriksaan & Triase')
            ->assertSee('Diperiksa hari ini')
            ->assertSee('Triase Merah')
            ->assertSee('Triase Kuning')
            ->set('shelterSearch', 'Baleendah')
            ->call('selectShelter', 1)
            ->assertSet('shelterFilter', '1')
            ->set('refugeeSearch', 'Ahmad Fauzi')
            ->call('selectRefugee', 1)
            ->assertSet('refugee_id', 1)
            ->set('complaint', 'Sesak napas sejak semalam, riwayat asma')
            ->set('temperature', '37,9')
            ->set('blood_pressure', '150/95')
            ->set('pulse', 112)
            ->set('triage', 'merah')
            ->set('action', 'Nebulisasi salbutamol dan pemberian oksigen')
            ->call('storeHealthCheck')
            ->assertHasNoErrors();

        Notification::assertSentTo($koordinatorBudi, CriticalTriageNotification::class);

        $created = HealthCheck::where('complaint', 'Sesak napas sejak semalam, riwayat asma')->firstOrFail();
        $this->assertEquals('37.9', (string) $created->temperature);

        // 2. Nakes pembuat mengubah catatan via panel kanan ("Ubah pemeriksaan: ...")
        Livewire::actingAs($nakesDewi)
            ->test(HealthCheckManagement::class)
            ->call('openEditModal', $created->id)
            ->assertSet('panelMode', 'edit')
            ->assertSee('Ubah pemeriksaan: Ahmad Fauzi')
            ->assertSee('Koordinator posko sudah dinotifikasi. Lanjutkan dengan membuat rujukan medis (PBI-09).')
            ->set('pulse', 98)
            ->call('updateHealthCheck')
            ->assertHasNoErrors();

        $this->assertEquals(98, $created->fresh()->pulse);

        // 3. Nakes pembuat menghapus catatan salah input via modal ("Hapus catatan pemeriksaan?")
        Livewire::actingAs($nakesDewi)
            ->test(HealthCheckManagement::class)
            ->call('openDeleteModal', $created->id)
            ->assertSet('showDeleteModal', true)
            ->assertSee('Hapus catatan pemeriksaan?')
            ->set('delete_reason', 'Salah memilih pengungsi')
            ->call('confirmDelete')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('health_checks', [
            'id' => $created->id,
            'delete_reason' => 'Salah memilih pengungsi',
        ]);
    }
}
