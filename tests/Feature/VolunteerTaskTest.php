<?php

namespace Tests\Feature;

use App\Livewire\Volunteer\TaskManager;
use App\Models\TaskAssignment;
use App\Models\User;
use App\Models\VolunteerProfile;
use App\Models\VolunteerTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class VolunteerTaskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function koordinator(): User
    {
        return User::where('email', 'koordinator.budi@resqhub.id')->firstOrFail();
    }

    private function relawan(string $email = 'relawan.rian@resqhub.id'): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function relawanBaru(string $name, float $lat, float $lng, int $skillId, string $availability = 'siap'): VolunteerProfile
    {
        $user = User::factory()->create([
            'name' => $name,
            'role' => 'relawan',
            'regency' => 'Kabupaten Bandung',
        ]);

        $profile = $user->volunteerProfile()->create([
            'domicile_lat' => $lat,
            'domicile_lng' => $lng,
            'radius_km' => 20,
            'availability' => $availability,
        ]);

        $profile->skills()->attach($skillId);

        return $profile;
    }

    private function tugasBaru(array $attributes = []): VolunteerTask
    {
        return VolunteerTask::create(array_merge([
            'disaster_event_id' => 1,
            'skill_id' => 1,
            'title' => 'Tugas Uji Coba',
            'description' => 'Deskripsi tugas uji coba.',
            'lat' => -6.9961,
            'lng' => 107.6238,
            'quota' => 2,
            'start_at' => Carbon::now()->addDay(),
            'end_at' => Carbon::now()->addDay()->addHours(3),
            'status' => 'terbuka',
            'created_by' => $this->koordinator()->id,
        ], $attributes));
    }

    public function test_koordinator_dapat_membuka_halaman_tugas(): void
    {
        $this->actingAs($this->koordinator())
            ->get('/admin/tasks')
            ->assertStatus(200)
            ->assertSee('Tugas Relawan')
            ->assertSee('Buat Tugas')
            ->assertSee('Daftar tugas');
    }

    public function test_relawan_dapat_membuka_halaman_tugas_saya(): void
    {
        $this->actingAs($this->relawan())
            ->get('/admin/tasks')
            ->assertStatus(200)
            ->assertSee('Tugas saya')
            ->assertDontSee('Buat Tugas');
    }

    public function test_warga_tidak_dapat_membuka_halaman_tugas(): void
    {
        $warga = User::where('role', 'warga')->firstOrFail();

        $this->actingAs($warga)
            ->get('/admin/tasks')
            ->assertStatus(403);
    }

    public function test_daftar_tugas_disaring_berdasarkan_status(): void
    {
        $this->actingAs($this->koordinator());

        Livewire::test(TaskManager::class)
            ->assertSee('Dapur Umum GOR Baleendah')
            ->call('setFilter', 'selesai')
            ->assertSet('statusFilter', 'selesai')
            ->assertSee('Distribusi Logistik Posko Al-Ikhlas')
            ->assertDontSee('Dapur Umum GOR Baleendah');
    }

    public function test_tugas_dapat_dibuat_dari_sos(): void
    {
        $this->actingAs($this->koordinator());

        $start = Carbon::now()->addDay()->format('Y-m-d\TH:i');
        $end = Carbon::now()->addDay()->addHours(3)->format('Y-m-d\TH:i');

        Livewire::test(TaskManager::class)
            ->call('openCreate')
            ->set('sosRequestId', 1)
            ->assertSet('title', fn ($title) => str_contains($title, 'Evakuasi'))
            ->set('skillId', 1)
            ->set('title', 'Evakuasi Warga RW 07')
            ->set('description', 'Evakuasi warga terdampak banjir.')
            ->set('quota', 2)
            ->set('startAt', $start)
            ->set('endAt', $end)
            ->set('lat', '-6.9961000')
            ->set('lng', '107.6238000')
            ->call('sendOffers')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('volunteer_tasks', [
            'title' => 'Evakuasi Warga RW 07',
            'sos_request_id' => 1,
            'quota' => 2,
            'status' => 'terbuka',
        ]);

        $this->assertDatabaseHas('sos_requests', ['id' => 1, 'status' => 'ditangani']);
    }

    public function test_rekomendasi_hanya_menampilkan_relawan_siap_dengan_keahlian_sesuai(): void
    {
        $dekat = $this->relawanBaru('Relawan Dekat', -6.9900, 107.6200, 1);
        $this->relawanBaru('Relawan Tidak Siap', -6.9901, 107.6201, 1, 'tidak_siap');
        $this->relawanBaru('Relawan Salah Keahlian', -6.9902, 107.6202, 3);

        $this->actingAs($this->koordinator());

        Livewire::test(TaskManager::class)
            ->call('openCreate')
            ->set('skillId', 1)
            ->set('startAt', Carbon::now()->addDay()->format('Y-m-d\TH:i'))
            ->set('endAt', Carbon::now()->addDay()->addHours(3)->format('Y-m-d\TH:i'))
            ->set('lat', '-6.9961000')
            ->set('lng', '107.6238000')
            ->assertSee('Rekomendasi relawan')
            ->assertSee($dekat->user->name)
            ->assertDontSee('Relawan Tidak Siap')
            ->assertDontSee('Relawan Salah Keahlian');
    }

    public function test_rekomendasi_diurutkan_berdasarkan_jarak(): void
    {
        $dekat = $this->relawanBaru('Relawan Radius Dekat', -6.9960, 107.6239, 1);
        $jauh = $this->relawanBaru('Relawan Radius Jauh', -6.8000, 107.9000, 1);

        $this->actingAs($this->koordinator());

        Livewire::test(TaskManager::class)
            ->call('openCreate')
            ->set('skillId', 1)
            ->set('startAt', Carbon::now()->addDay()->format('Y-m-d\TH:i'))
            ->set('endAt', Carbon::now()->addDay()->addHours(3)->format('Y-m-d\TH:i'))
            ->set('lat', '-6.9961000')
            ->set('lng', '107.6238000')
            ->assertSeeInOrder([$dekat->user->name, $jauh->user->name]);
    }

    public function test_relawan_dengan_jadwal_bentrok_tidak_direkomendasikan(): void
    {
        $bentrok = $this->relawanBaru('Relawan Jadwal Bentrok', -6.9900, 107.6200, 1);

        $this->tugasBaru([
            'title' => 'Tugas Bentrok',
            'start_at' => Carbon::now()->addDay(),
            'end_at' => Carbon::now()->addDay()->addHours(2),
            'status' => 'berjalan',
        ])->assignments()->create([
            'volunteer_profile_id' => $bentrok->id,
            'status' => 'diterima',
        ]);

        $this->actingAs($this->koordinator());

        Livewire::test(TaskManager::class)
            ->call('openCreate')
            ->set('skillId', 1)
            ->set('startAt', Carbon::now()->addDay()->addHour()->format('Y-m-d\TH:i'))
            ->set('endAt', Carbon::now()->addDay()->addHours(3)->format('Y-m-d\TH:i'))
            ->set('lat', '-6.9961000')
            ->set('lng', '107.6238000')
            ->assertDontSee('Relawan Jadwal Bentrok');
    }

    public function test_penawaran_dikirim_maksimal_sesuai_kuota(): void
    {
        $satu = $this->relawanBaru('Relawan Kuota Satu', -6.9900, 107.6200, 1);
        $dua = $this->relawanBaru('Relawan Kuota Dua', -6.9910, 107.6210, 1);
        $tiga = $this->relawanBaru('Relawan Kuota Tiga', -6.9920, 107.6220, 1);

        $this->actingAs($this->koordinator());

        $component = Livewire::test(TaskManager::class)
            ->call('openCreate')
            ->set('skillId', 1)
            ->set('quota', 2)
            ->set('title', 'Tugas Kuota Terbatas')
            ->set('description', 'Kuota hanya dua relawan.')
            ->set('startAt', Carbon::now()->addDay()->format('Y-m-d\TH:i'))
            ->set('endAt', Carbon::now()->addDay()->addHours(3)->format('Y-m-d\TH:i'))
            ->set('lat', '-6.9961000')
            ->set('lng', '107.6238000')
            ->call('toggleProfile', $satu->id)
            ->call('toggleProfile', $dua->id)
            ->call('toggleProfile', $tiga->id)
            ->assertSet('notificationType', 'error')
            ->assertCount('selectedProfiles', 2)
            ->call('sendOffers')
            ->assertHasNoErrors();

        $task = VolunteerTask::where('title', 'Tugas Kuota Terbatas')->firstOrFail();

        $this->assertSame(2, $task->assignments()->count());
        $this->assertSame(2, $task->assignments()->where('status', 'ditawarkan')->count());
        $this->assertCount(1, $satu->user->notifications);
        $this->assertCount(0, $tiga->user->notifications);
    }

    public function test_tugas_selesai_tidak_dapat_diubah(): void
    {
        $this->actingAs($this->koordinator());

        Livewire::test(TaskManager::class)
            ->call('openEdit', 2)
            ->assertSet('notificationType', 'error')
            ->assertSet('notification', 'Tugas yang sudah selesai tidak dapat diubah.');
    }

    public function test_perubahan_tugas_mengirim_notifikasi_ke_relawan(): void
    {
        $relawan = $this->relawan();
        $task = $this->tugasBaru([
            'title' => 'Tugas Yang Diubah',
            'start_at' => Carbon::now()->addDays(2),
            'end_at' => Carbon::now()->addDays(2)->addHours(3),
        ]);

        $task->assignments()->create([
            'volunteer_profile_id' => $relawan->volunteerProfile->id,
            'status' => 'diterima',
        ]);

        $before = $relawan->notifications()->count();

        $this->actingAs($this->koordinator());

        Livewire::test(TaskManager::class)
            ->call('openEdit', $task->id)
            ->assertSet('showForm', true)
            ->set('description', 'Deskripsi terbaru setelah perubahan.')
            ->set('quota', 3)
            ->call('saveChanges')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('volunteer_tasks', [
            'id' => $task->id,
            'description' => 'Deskripsi terbaru setelah perubahan.',
            'quota' => 3,
        ]);

        $this->assertSame($before + 1, $relawan->notifications()->count());
    }

    public function test_ubah_tugas_dapat_sekaligus_mengirim_penawaran_baru(): void
    {
        $relawan = $this->relawanBaru('Relawan Tambahan', -6.9900, 107.6200, 1);
        $task = $this->tugasBaru(['quota' => 2, 'title' => 'Tugas Tambah Relawan']);

        $this->actingAs($this->koordinator());

        Livewire::test(TaskManager::class)
            ->call('openEdit', $task->id)
            ->assertSet('showForm', true)
            ->set('skillId', 1)
            ->call('toggleProfile', $relawan->id)
            ->call('saveChanges')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('task_assignments', [
            'volunteer_task_id' => $task->id,
            'volunteer_profile_id' => $relawan->id,
            'status' => 'ditawarkan',
        ]);
        $this->assertCount(1, $relawan->user->notifications);
    }

    public function test_tombol_buat_tugas_pada_sos_membuka_form_terisi(): void
    {
        $this->actingAs($this->koordinator())
            ->get('/admin/tasks?sos=1')
            ->assertStatus(200)
            ->assertSee('Buat tugas')
            ->assertSee('Evakuasi', false);
    }

    public function test_koordinator_dapat_menawar_ulang_relawan_yang_ditolak(): void
    {
        $relawan = $this->relawan();
        $task = $this->tugasBaru(['quota' => 2, 'title' => 'Tugas Tawar Ulang']);
        $assignment = $task->assignments()->create([
            'volunteer_profile_id' => $relawan->volunteerProfile->id,
            'status' => 'ditolak',
            'reject_reason' => 'Ditolak oleh relawan.',
        ]);

        $before = $relawan->notifications()->count();

        $this->actingAs($this->koordinator());

        Livewire::test(TaskManager::class)
            ->call('reoffer', $assignment->id)
            ->assertSet('notificationType', 'success');

        $this->assertDatabaseHas('task_assignments', [
            'id' => $assignment->id,
            'status' => 'ditawarkan',
            'reject_reason' => null,
        ]);
        $this->assertSame($before + 1, $relawan->notifications()->count());
    }

    public function test_relawan_tidak_dapat_menawar_ulang_penugasan(): void
    {
        $relawan = $this->relawan();
        $assignment = TaskAssignment::where('volunteer_profile_id', $relawan->volunteerProfile->id)->firstOrFail();
        $assignment->update(['status' => 'ditolak']);

        $this->actingAs($relawan);

        Livewire::test(TaskManager::class)->call('reoffer', $assignment->id);

        $this->assertDatabaseHas('task_assignments', ['id' => $assignment->id, 'status' => 'ditolak']);
    }

    public function test_koordinator_dapat_menyelesaikan_tugas_berjalan(): void
    {
        $relawan = $this->relawan();
        $task = $this->tugasBaru(['quota' => 1, 'status' => 'berjalan', 'title' => 'Tugas Berjalan']);
        $assignment = $task->assignments()->create([
            'volunteer_profile_id' => $relawan->volunteerProfile->id,
            'status' => 'diterima',
        ]);

        $before = $relawan->notifications()->count();

        $this->actingAs($this->koordinator());

        Livewire::test(TaskManager::class)
            ->call('completeTask', $task->id)
            ->assertSet('notificationType', 'success');

        $this->assertDatabaseHas('volunteer_tasks', ['id' => $task->id, 'status' => 'selesai']);
        $this->assertDatabaseHas('task_assignments', ['id' => $assignment->id, 'status' => 'selesai']);
        $this->assertSame($before + 1, $relawan->notifications()->count());
    }

    public function test_tugas_tanpa_relawan_diterima_tidak_dapat_diselesaikan(): void
    {
        $task = $this->tugasBaru(['status' => 'berjalan', 'title' => 'Tugas Tanpa Relawan']);

        $this->actingAs($this->koordinator());

        Livewire::test(TaskManager::class)
            ->call('completeTask', $task->id)
            ->assertSet('notificationType', 'error');

        $this->assertDatabaseHas('volunteer_tasks', ['id' => $task->id, 'status' => 'berjalan']);
    }

    public function test_relawan_tidak_dapat_menyelesaikan_tugas(): void
    {
        $relawan = $this->relawan();
        $task = $this->tugasBaru(['quota' => 1, 'status' => 'berjalan']);
        $task->assignments()->create([
            'volunteer_profile_id' => $relawan->volunteerProfile->id,
            'status' => 'diterima',
        ]);

        $this->actingAs($relawan);

        Livewire::test(TaskManager::class)->call('completeTask', $task->id);

        $this->assertDatabaseHas('volunteer_tasks', ['id' => $task->id, 'status' => 'berjalan']);
    }

    public function test_batalkan_tugas_meminta_alasan_wajib(): void
    {
        $task = $this->tugasBaru(['title' => 'Tugas Batal Tanpa Alasan']);

        $this->actingAs($this->koordinator());

        Livewire::test(TaskManager::class)
            ->call('requestCancel', $task->id)
            ->assertSet('showCancelModal', true)
            ->set('cancelReason', '')
            ->call('cancelTask', $task->id)
            ->assertHasErrors(['cancelReason' => 'required']);

        $this->assertDatabaseHas('volunteer_tasks', ['id' => $task->id, 'status' => 'terbuka']);
    }

    public function test_batalkan_tugas_membatalkan_seluruh_penawaran_dan_mengirim_notifikasi(): void
    {
        $relawan = $this->relawan();
        $task = $this->tugasBaru(['title' => 'Tugas Batal Penawaran']);

        $ditawarkan = $task->assignments()->create([
            'volunteer_profile_id' => $relawan->volunteerProfile->id,
            'status' => 'ditawarkan',
        ]);
        $diterima = $task->assignments()->create([
            'volunteer_profile_id' => $this->relawan('relawan.agus@resqhub.id')->volunteerProfile->id,
            'status' => 'diterima',
        ]);

        $before = $relawan->notifications()->count();

        $this->actingAs($this->koordinator());

        Livewire::test(TaskManager::class)
            ->call('requestCancel', $task->id)
            ->set('cancelReason', 'Posko sudah ditutup.')
            ->call('cancelTask', $task->id)
            ->assertHasNoErrors()
            ->assertSet('notificationType', 'success');

        $this->assertDatabaseHas('volunteer_tasks', [
            'id' => $task->id,
            'status' => 'dibatalkan',
            'cancel_reason' => 'Posko sudah ditutup.',
        ]);
        $this->assertDatabaseHas('task_assignments', ['id' => $ditawarkan->id, 'status' => 'ditolak']);
        $this->assertDatabaseHas('task_assignments', ['id' => $diterima->id, 'status' => 'ditolak']);
        $this->assertSame($before + 1, $relawan->notifications()->count());

        $notification = $relawan->notifications()->latest('id')->first();
        $this->assertSame('task_cancelled', $notification->data['type']);
        $this->assertSame('Posko sudah ditutup.', $notification->data['reason']);
        $this->assertStringContainsString('Posko sudah ditutup.', $notification->data['message']);
    }

    public function test_tugas_berjalan_tidak_dapat_dibatalkan(): void
    {
        $task = $this->tugasBaru(['status' => 'berjalan', 'title' => 'Tugas Berjalan Batal']);

        $this->actingAs($this->koordinator());

        Livewire::test(TaskManager::class)
            ->call('requestCancel', $task->id)
            ->assertSet('showCancelModal', false)
            ->assertSet('notificationType', 'error');

        $this->assertDatabaseHas('volunteer_tasks', ['id' => $task->id, 'status' => 'berjalan']);
    }

    public function test_relawan_tidak_dapat_membatalkan_tugas(): void
    {
        $task = $this->tugasBaru(['title' => 'Tugas Batal Relawan']);

        $this->actingAs($this->relawan());

        Livewire::test(TaskManager::class)
            ->call('requestCancel', $task->id)
            ->assertSet('showCancelModal', false);

        $this->assertDatabaseHas('volunteer_tasks', ['id' => $task->id, 'status' => 'terbuka']);
    }

    public function test_relawan_menerima_penawaran_dan_tugas_menjadi_berjalan(): void
    {
        $relawan = $this->relawan();
        $task = $this->tugasBaru(['quota' => 1, 'title' => 'Tugas Kuota Satu']);
        $assignment = $task->assignments()->create([
            'volunteer_profile_id' => $relawan->volunteerProfile->id,
            'status' => 'ditawarkan',
        ]);

        $this->actingAs($relawan);

        Livewire::test(TaskManager::class)
            ->call('acceptOffer', $assignment->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('task_assignments', ['id' => $assignment->id, 'status' => 'diterima']);
        $this->assertDatabaseHas('volunteer_tasks', ['id' => $task->id, 'status' => 'berjalan']);
    }

    public function test_detail_tugas_menampilkan_status_penawaran_setiap_relawan(): void
    {
        $this->actingAs($this->koordinator());

        Livewire::test(TaskManager::class)
            ->call('openDetail', 1)
            ->assertSet('detailId', 1)
            ->assertSee('Status penawaran relawan')
            ->assertSee($this->relawan()->name)
            ->assertSee('Diterima');
    }
}
