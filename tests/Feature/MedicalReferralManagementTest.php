<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\MedicalReferralManagement;
use App\Models\ActivityLog;
use App\Models\HealthCheck;
use App\Models\MedicalReferral;
use App\Models\Refugee;
use App\Models\User;
use App\Notifications\MedicalReferralCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class MedicalReferralManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_nakes_can_create_referral_from_health_check_and_notify_coordinator(): void
    {
        Notification::fake();

        $nakes = User::where('email', 'nakes.hendra@resqhub.id')->firstOrFail();
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();
        $refugee = Refugee::findOrFail(1); // Posko 1 (Koordinator: Budi)

        // Buat pemeriksaan Triase Merah baru yang belum memiliki rujukan
        $check = HealthCheck::create([
            'refugee_id' => $refugee->id,
            'nakes_id' => $nakes->id,
            'complaint' => 'Sesak napas akut dan nyeri dada.',
            'temperature' => 38.4,
            'blood_pressure' => '165/100',
            'pulse' => 116,
            'triage' => 'merah',
            'action' => 'Pasang oksigen 5 lpm, siapkan ambulans.',
            'checked_at' => now(),
        ]);

        $response = $this->actingAs($nakes)->postJson(route('admin.medical-referrals.store'), [
            'health_check_id' => $check->id,
            'facility_name' => 'RSUD Al-Ihsan Baleendah',
            'reason' => 'Membutuhkan penanganan IGD dan pemeriksaan EKG segera.',
            'patient_condition' => 'Pasien sadar, sesak berat, terpasang oksigen nasal kanul.',
            'transport_needed' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.health_check_id', $check->id)
            ->assertJsonPath('data.status', MedicalReferral::STATUS_DIAJUKAN)
            ->assertJsonPath('data.status_label', 'Diajukan')
            ->assertJsonPath('data.transport_needed', true);

        $referralId = $response->json('data.id');

        Notification::assertSentTo(
            $koordinatorBudi,
            MedicalReferralCreatedNotification::class,
            function (MedicalReferralCreatedNotification $notification) use ($koordinatorBudi, $referralId, $check) {
                $payload = $notification->toArray($koordinatorBudi);

                return $payload['medical_referral_id'] === $referralId
                    && $payload['health_check_id'] === $check->id
                    && $payload['transport_needed'] === true;
            }
        );

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $nakes->id,
            'subject_type' => MedicalReferral::class,
            'subject_id' => $referralId,
            'action' => 'created_medical_referral',
        ]);

        // Duplikasi rujukan aktif untuk pemeriksaan yang sama harus ditolak (422)
        $this->actingAs($nakes)
            ->postJson(route('admin.medical-referrals.store'), [
                'health_check_id' => $check->id,
                'facility_name' => 'RSUP dr. Hasan Sadikin',
                'reason' => 'Duplikat rujukan.',
                'patient_condition' => 'Masih stabil.',
                'transport_needed' => false,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['health_check_id']);
    }

    public function test_referrals_list_filter_and_detail_enforce_role_and_shelter_scope(): void
    {
        $nakes = User::where('email', 'nakes.hendra@resqhub.id')->firstOrFail();
        $admin = User::where('role', 'admin')->firstOrFail();
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();
        $koordinatorSiti = User::where('email', 'koordinator.siti@resqhub.id')->firstOrFail();
        $warga = User::where('role', 'warga')->firstOrFail();
        $relawan = User::where('role', 'relawan')->firstOrFail();

        // Seeder referral ID 1 berada di Posko 1 (Koordinator: Budi) dengan status dalam_perjalanan
        $this->actingAs($nakes)
            ->getJson(route('admin.medical-referrals.index', ['status' => 'dalam_perjalanan', 'shelter_id' => 1]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 1);

        $this->actingAs($nakes)
            ->getJson(route('admin.medical-referrals.index', ['status' => 'selesai']))
            ->assertOk()
            ->assertJsonCount(0, 'data');

        // Detail menampilkan pemeriksaan asal dan riwayat status
        $this->actingAs($koordinatorBudi)
            ->getJson(route('admin.medical-referrals.show', 1))
            ->assertOk()
            ->assertJsonPath('data.id', 1)
            ->assertJsonPath('data.health_check.id', 1)
            ->assertJsonStructure(['data' => ['health_check'], 'history']);

        // Admin Pusdalops dapat melihat daftar rujukan
        $this->actingAs($admin)
            ->getJson(route('admin.medical-referrals.index'))
            ->assertOk();

        // Koordinator Posko 2 (Siti) ditolak melihat rujukan Posko 1
        $this->actingAs($koordinatorSiti)
            ->getJson(route('admin.medical-referrals.show', 1))
            ->assertForbidden();

        // Warga & Relawan ditolak mengakses rujukan
        $this->actingAs($warga)->getJson(route('admin.medical-referrals.index'))->assertForbidden();
        $this->actingAs($relawan)->getJson(route('admin.medical-referrals.index'))->assertForbidden();

        // Koordinator tidak dapat membuat rujukan baru
        $this->actingAs($koordinatorBudi)
            ->postJson(route('admin.medical-referrals.store'), [
                'health_check_id' => 2,
                'facility_name' => 'Puskesmas Baleendah',
                'reason' => 'Coba buat oleh koordinator',
                'patient_condition' => 'Stabil',
                'transport_needed' => false,
            ])
            ->assertForbidden();
    }

    public function test_nakes_and_related_coordinator_can_update_status_and_completed_requires_outcome(): void
    {
        $nakes = User::where('email', 'nakes.hendra@resqhub.id')->firstOrFail();
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();
        $koordinatorSiti = User::where('email', 'koordinator.siti@resqhub.id')->firstOrFail();

        // Referral ID 1 berstatus dalam_perjalanan di Posko 1
        // 1. Koordinator posko lain ditolak (403)
        $this->actingAs($koordinatorSiti)
            ->putJson(route('admin.medical-referrals.update', 1), [
                'status' => MedicalReferral::STATUS_DITERIMA_FASKES,
                'progress_note' => 'Coba update dari posko lain.',
            ])
            ->assertForbidden();

        // 2. Status tidak boleh mundur (dalam_perjalanan -> diajukan) -> 422
        $this->actingAs($koordinatorBudi)
            ->putJson(route('admin.medical-referrals.update', 1), [
                'status' => MedicalReferral::STATUS_DIAJUKAN,
                'progress_note' => 'Mundur status.',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        // 3. Koordinator Budi memperbarui status ke Diterima Faskes + catatan perkembangan
        $this->actingAs($koordinatorBudi)
            ->putJson(route('admin.medical-referrals.update', 1), [
                'status' => MedicalReferral::STATUS_DITERIMA_FASKES,
                'progress_note' => 'Pasien diterima di IGD RSUD Al-Ihsan pukul 10.15.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', MedicalReferral::STATUS_DITERIMA_FASKES)
            ->assertJsonPath('data.status_label', 'Diterima Faskes');

        // 4. Status Selesai tanpa hasil akhir (outcome) harus ditolak (422)
        $this->actingAs($nakes)
            ->putJson(route('admin.medical-referrals.update', 1), [
                'status' => MedicalReferral::STATUS_SELESAI,
                'progress_note' => 'Penanganan selesai.',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['outcome']);

        // 5. Status Selesai dengan hasil akhir (dirawat / kembali_ke_posko) berhasil
        $this->actingAs($nakes)
            ->putJson(route('admin.medical-referrals.update', 1), [
                'status' => MedicalReferral::STATUS_SELESAI,
                'outcome' => MedicalReferral::OUTCOME_DIRAWAT,
                'progress_note' => 'Pasien dipindahkan ke ruang rawat inap paru.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', MedicalReferral::STATUS_SELESAI)
            ->assertJsonPath('data.outcome', MedicalReferral::OUTCOME_DIRAWAT)
            ->assertJsonPath('data.outcome_label', 'Dirawat di faskes');

        // 6. Rujukan yang sudah Selesai tidak dapat diperbarui lagi
        $this->actingAs($nakes)
            ->putJson(route('admin.medical-referrals.update', 1), [
                'status' => MedicalReferral::STATUS_SELESAI,
                'outcome' => MedicalReferral::OUTCOME_KEMBALI_KE_POSKO,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        $this->assertEquals(
            2,
            ActivityLog::where('subject_type', MedicalReferral::class)
                ->where('subject_id', 1)
                ->where('action', 'updated_medical_referral_status')
                ->count()
        );
    }

    public function test_only_nakes_can_cancel_diajukan_referral_with_mandatory_reason(): void
    {
        Notification::fake();

        $nakes = User::where('email', 'nakes.hendra@resqhub.id')->firstOrFail();
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();

        // 1. Rujukan ID 1 berstatus dalam_perjalanan tidak boleh dibatalkan -> 422
        $this->actingAs($nakes)
            ->deleteJson(route('admin.medical-referrals.destroy', 1), [
                'cancel_reason' => 'Coba batalkan rujukan yang sudah berjalan',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cancel']);

        // 2. Buat rujukan baru berstatus Diajukan pada HealthCheck ID 2
        $created = $this->actingAs($nakes)
            ->postJson(route('admin.medical-referrals.store'), [
                'health_check_id' => 2,
                'facility_name' => 'RSUD Majalaya',
                'reason' => 'Observasi kehamilan risiko tinggi.',
                'patient_condition' => 'Ibu hamil pusing, TD 130/85.',
                'transport_needed' => true,
            ])
            ->assertCreated();

        $newId = $created->json('data.id');

        // 3. Koordinator tidak berwenang membatalkan rujukan -> 403
        $this->actingAs($koordinatorBudi)
            ->deleteJson(route('admin.medical-referrals.destroy', $newId), [
                'cancel_reason' => 'Coba batal oleh koordinator',
            ])
            ->assertForbidden();

        // 4. Pembatalan tanpa alasan ditolak -> 422
        $this->actingAs($nakes)
            ->deleteJson(route('admin.medical-referrals.destroy', $newId), [
                'cancel_reason' => '',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cancel_reason']);

        // 5. Pembatalan oleh nakes pada status Diajukan dengan alasan berhasil
        $this->actingAs($nakes)
            ->deleteJson(route('admin.medical-referrals.destroy', $newId), [
                'cancel_reason' => 'Kondisi membaik setelah observasi',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', MedicalReferral::STATUS_DIBATALKAN)
            ->assertJsonPath('data.cancel_reason', 'Kondisi membaik setelah observasi');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $nakes->id,
            'subject_type' => MedicalReferral::class,
            'subject_id' => $newId,
            'action' => 'cancelled_medical_referral',
        ]);
    }

    public function test_livewire_medical_referral_management_and_dashboard_widget_work_end_to_end(): void
    {
        Notification::fake();

        $nakes = User::where('email', 'nakes.hendra@resqhub.id')->firstOrFail();
        $koordinatorBudi = User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();

        // 1. Nakes membuat rujukan baru melalui Livewire (dari HealthCheck ID 2)
        Livewire::actingAs($nakes)
            ->test(MedicalReferralManagement::class)
            ->assertStatus(200)
            ->assertSee('Rujukan Medis')
            ->call('selectHealthCheck', 2)
            ->set('facility_name', 'RSUP dr. Hasan Sadikin')
            ->set('reason', 'Pemantauan preeklamsia kehamilan 32 minggu')
            ->set('patient_condition', 'Pasien sadar, tekanan darah meningkat')
            ->set('transport_needed', true)
            ->call('storeReferral')
            ->assertHasNoErrors()
            ->assertSet('panelMode', 'detail')
            ->assertSee('RSUP dr. Hasan Sadikin')
            ->assertSee('Pemeriksaan Asal')
            ->assertSee('Riwayat Status');

        Notification::assertSentTo($koordinatorBudi, MedicalReferralCreatedNotification::class);

        $created = MedicalReferral::where('facility_name', 'RSUP dr. Hasan Sadikin')->firstOrFail();

        // 2. Koordinator membuka panel ubah, memperbarui status ke Selesai dengan hasil akhir
        Livewire::actingAs($koordinatorBudi)
            ->test(MedicalReferralManagement::class)
            ->call('openEditPanel', $created->id)
            ->assertSet('panelMode', 'edit')
            ->set('status', MedicalReferral::STATUS_SELESAI)
            ->set('outcome', MedicalReferral::OUTCOME_KEMBALI_KE_POSKO)
            ->set('progress_note', 'Pasien sudah diperiksa dokter kandungan dan kembali ke posko.')
            ->call('updateReferral')
            ->assertHasNoErrors()
            ->assertSet('panelMode', 'detail');

        $this->assertEquals(MedicalReferral::STATUS_SELESAI, $created->fresh()->status);
        $this->assertEquals(MedicalReferral::OUTCOME_KEMBALI_KE_POSKO, $created->fresh()->outcome);

        // 3. Nakes membuat rujukan lain (HealthCheck ID 3) lalu membatalkannya via modal
        Livewire::actingAs($nakes)
            ->test(MedicalReferralManagement::class)
            ->call('selectHealthCheck', 3)
            ->set('facility_name', 'Puskesmas Baleendah')
            ->set('reason', 'Evaluasi alergi kulit')
            ->set('patient_condition', 'Ruam ringan pada tangan')
            ->set('transport_needed', false)
            ->call('storeReferral')
            ->assertHasNoErrors();

        $toCancel = MedicalReferral::where('facility_name', 'Puskesmas Baleendah')->firstOrFail();

        Livewire::actingAs($nakes)
            ->test(MedicalReferralManagement::class)
            ->call('openCancelModal', $toCancel->id)
            ->assertSet('showCancelModal', true)
            ->set('cancel_reason', 'Kondisi membaik setelah observasi')
            ->call('confirmCancel')
            ->assertHasNoErrors()
            ->assertSet('showCancelModal', false);

        $this->assertEquals(MedicalReferral::STATUS_DIBATALKAN, $toCancel->fresh()->status);

        // 4. Dashboard menampilkan widget ringkasan rujukan medis
        Livewire::actingAs($nakes)
            ->test(Dashboard::class)
            ->assertStatus(200)
            ->assertSee('Rujukan aktif')
            ->assertSee('Kelola rujukan');
    }
}
