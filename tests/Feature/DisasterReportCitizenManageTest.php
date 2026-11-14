<?php

namespace Tests\Feature;

use App\Livewire\Report\EditReport;
use App\Livewire\Report\MyReports;
use App\Livewire\Report\ShowReport;
use App\Models\ActivityLog;
use App\Models\DisasterReport;
use App\Models\User;
use App\Services\DisasterReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DisasterReportCitizenManageTest extends TestCase
{
    use RefreshDatabase;

    private User $warga;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');

        $this->warga = User::factory()->create(['role' => 'warga']);
    }

    /**
     * Laporan milik warga yang dibuat lewat service agar riwayat dan foto ikut tercatat.
     */
    private function makeReport(int $photoCount = 0, array $overrides = []): DisasterReport
    {
        $photos = [];

        for ($i = 1; $i <= $photoCount; $i++) {
            $photos[] = UploadedFile::fake()->image("foto-{$i}.jpg");
        }

        return app(DisasterReportService::class)->create($this->warga, array_merge([
            'disaster_type' => 'banjir',
            'title' => 'Banjir di Baleendah',
            'description' => 'Air setinggi lutut orang dewasa.',
            'estimated_affected' => 40,
            'lat' => -7.0021,
            'lng' => 107.6289,
            'address' => 'Jl. Raya Baleendah',
            'occurred_at' => now()->subHours(2),
        ], $overrides), $photos);
    }

    public function test_owner_can_open_detail_and_edit_pages(): void
    {
        $report = $this->makeReport();

        $this->actingAs($this->warga)->get(route('admin.reports.show', $report))
            ->assertOk()
            ->assertSee('Banjir di Baleendah')
            ->assertSee('Riwayat Status')
            ->assertSee('Laporan dikirim');

        $this->actingAs($this->warga)->get(route('admin.reports.edit', $report))
            ->assertOk()
            ->assertSee('Ubah Laporan Kejadian');
    }

    public function test_other_warga_cannot_open_detail_or_edit_pages(): void
    {
        $report = $this->makeReport();
        $other = User::factory()->create(['role' => 'warga']);

        $this->actingAs($other)->get(route('admin.reports.show', $report))->assertForbidden();
        $this->actingAs($other)->get(route('admin.reports.edit', $report))->assertForbidden();
    }

    public function test_verified_report_cannot_be_edited(): void
    {
        $report = $this->makeReport();
        $report->update(['status' => DisasterReport::STATUS_ACCEPTED]);

        $this->actingAs($this->warga)->get(route('admin.reports.edit', $report))->assertForbidden();

        $this->actingAs($this->warga)->get(route('admin.reports.show', $report))
            ->assertOk()
            ->assertSee('tidak dapat diubah atau dihapus')
            ->assertDontSee('wire:click="delete"', false);
    }

    public function test_edit_form_is_prefilled(): void
    {
        $report = $this->makeReport();

        Livewire::actingAs($this->warga)
            ->test(EditReport::class, ['report' => $report])
            ->assertSet('title', 'Banjir di Baleendah')
            ->assertSet('disaster_type', 'banjir')
            ->assertSet('lat', -7.0021)
            ->assertSet('occurred_at', $report->occurred_at->format('Y-m-d\TH:i'));
    }

    public function test_owner_can_update_content_location_and_photos(): void
    {
        $report = $this->makeReport(2);
        $removed = $report->photos->first();

        Livewire::actingAs($this->warga)
            ->test(EditReport::class, ['report' => $report])
            ->set('title', 'Banjir meluas di Baleendah')
            ->set('lat', -7.0100)
            ->set('lng', 107.6300)
            ->call('removeExistingPhoto', $removed->id)
            ->set('newPhotos', [UploadedFile::fake()->image('baru.png')])
            ->assertCount('photos', 1)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.reports.show', $report));

        $report->refresh();

        $this->assertSame('Banjir meluas di Baleendah', $report->title);
        $this->assertSame(-7.01, $report->lat);
        $this->assertCount(2, $report->photos);
        $this->assertDatabaseMissing('report_photos', ['id' => $removed->id]);
        Storage::disk('public')->assertMissing($removed->path);
        $this->assertTrue(ActivityLog::where('action', 'laporan_diubah')->where('subject_id', $report->id)->exists());
    }

    public function test_edit_keeps_total_photos_at_three(): void
    {
        $report = $this->makeReport(2);

        Livewire::actingAs($this->warga)
            ->test(EditReport::class, ['report' => $report])
            ->set('newPhotos', [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
            ])
            ->assertCount('photos', 1)
            ->assertHasErrors('photos');
    }

    public function test_removed_photo_cannot_be_restored_when_slots_are_full(): void
    {
        $report = $this->makeReport(3);
        $photoId = $report->photos->first()->id;

        Livewire::actingAs($this->warga)
            ->test(EditReport::class, ['report' => $report])
            ->call('removeExistingPhoto', $photoId)
            ->set('newPhotos', [UploadedFile::fake()->image('pengganti.jpg')])
            ->assertCount('photos', 1)
            ->call('restoreExistingPhoto', $photoId)
            ->assertHasErrors('photos')
            ->assertSet('removed_photo_ids', [$photoId]);
    }

    public function test_update_validates_required_fields(): void
    {
        $report = $this->makeReport();

        Livewire::actingAs($this->warga)
            ->test(EditReport::class, ['report' => $report])
            ->set('title', '')
            ->set('occurred_at', now()->addDay()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['title' => 'required', 'occurred_at' => 'before_or_equal']);

        $this->assertSame('Banjir di Baleendah', $report->fresh()->title);
    }

    public function test_update_is_blocked_after_report_is_verified_mid_edit(): void
    {
        $report = $this->makeReport();

        $component = Livewire::actingAs($this->warga)->test(EditReport::class, ['report' => $report]);

        $report->update(['status' => DisasterReport::STATUS_REJECTED, 'reject_reason' => 'Duplikat']);

        $component->set('title', 'Judul susulan')->call('save')->assertForbidden();

        $this->assertSame('Banjir di Baleendah', $report->fresh()->title);
    }

    public function test_owner_can_delete_pending_report_from_detail(): void
    {
        $report = $this->makeReport(1);
        $path = $report->photos->first()->path;

        Livewire::actingAs($this->warga)
            ->test(ShowReport::class, ['report' => $report])
            ->call('delete')
            ->assertRedirect(route('admin.reports.index'));

        $this->assertDatabaseMissing('disaster_reports', ['id' => $report->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_owner_can_delete_pending_report_from_list(): void
    {
        $report = $this->makeReport();

        Livewire::actingAs($this->warga)
            ->test(MyReports::class)
            ->assertSee('Lihat Detail')
            ->call('delete', $report->id)
            ->assertDontSee('Banjir di Baleendah');

        $this->assertDatabaseMissing('disaster_reports', ['id' => $report->id]);
    }

    public function test_verified_report_cannot_be_deleted(): void
    {
        $report = $this->makeReport();
        $report->update(['status' => DisasterReport::STATUS_ACCEPTED]);

        Livewire::actingAs($this->warga)
            ->test(ShowReport::class, ['report' => $report])
            ->call('delete')
            ->assertForbidden();

        Livewire::actingAs($this->warga)
            ->test(MyReports::class)
            ->call('delete', $report->id)
            ->assertForbidden();

        $this->assertDatabaseHas('disaster_reports', ['id' => $report->id]);
    }

    public function test_other_warga_cannot_delete_report(): void
    {
        $report = $this->makeReport();
        $other = User::factory()->create(['role' => 'warga']);

        Livewire::actingAs($other)
            ->test(MyReports::class)
            ->call('delete', $report->id)
            ->assertForbidden();

        $this->assertDatabaseHas('disaster_reports', ['id' => $report->id]);
    }

    public function test_detail_shows_status_history_and_reject_reason(): void
    {
        $report = $this->makeReport();
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Pusdalops']);

        app(DisasterReportService::class)->reject($admin, $report, 'Lokasi tidak sesuai foto');

        Livewire::actingAs($this->warga)
            ->test(ShowReport::class, ['report' => $report])
            ->assertSee('Ditolak')
            ->assertSeeInOrder(['Laporan dikirim', 'Laporan ditolak'])
            ->assertSee('Admin Pusdalops')
            ->assertSee('Lokasi tidak sesuai foto');
    }
}
