<?php

namespace Tests\Feature;

use App\Livewire\Report\CreateReport;
use App\Livewire\Report\MyReports;
use App\Models\DisasterReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DisasterReportCitizenPageTest extends TestCase
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

    public function test_warga_can_open_report_list_and_create_pages(): void
    {
        $this->actingAs($this->warga)->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Laporan Kejadian Saya');

        $this->actingAs($this->warga)->get(route('admin.reports.create'))
            ->assertOk()
            ->assertSee('Buat Laporan Kejadian')
            ->assertSee('leaflet', false);
    }

    public function test_non_warga_cannot_open_report_pages(): void
    {
        foreach (['admin', 'koordinator', 'relawan'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('admin.reports.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.reports.create'))->assertForbidden();
        }
    }

    public function test_report_list_only_shows_own_reports(): void
    {
        DisasterReport::factory()->create(['reporter_id' => $this->warga->id, 'title' => 'Banjir Laporan Saya']);
        DisasterReport::factory()->create(['title' => 'Longsor Laporan Orang Lain']);

        Livewire::actingAs($this->warga)
            ->test(MyReports::class)
            ->assertSee('Banjir Laporan Saya')
            ->assertSee('Menunggu Verifikasi')
            ->assertDontSee('Longsor Laporan Orang Lain');
    }

    public function test_report_list_shows_reject_reason(): void
    {
        DisasterReport::factory()->create([
            'reporter_id' => $this->warga->id,
            'status' => DisasterReport::STATUS_REJECTED,
            'reject_reason' => 'Lokasi tidak sesuai foto',
        ]);

        Livewire::actingAs($this->warga)
            ->test(MyReports::class)
            ->assertSee('Ditolak')
            ->assertSee('Lokasi tidak sesuai foto');
    }

    public function test_warga_can_submit_report_with_photos(): void
    {
        Livewire::actingAs($this->warga)
            ->test(CreateReport::class)
            ->set('disaster_type', 'banjir')
            ->set('title', 'Banjir di Dayeuhkolot')
            ->set('description', 'Air setinggi lutut orang dewasa, akses jalan tertutup.')
            ->set('estimated_affected', '')
            ->set('lat', -6.9871)
            ->set('lng', 107.6253)
            ->set('address', 'Jl. Raya Dayeuhkolot')
            ->set('occurred_at', now()->subHour()->format('Y-m-d\TH:i'))
            ->set('newPhotos', [
                UploadedFile::fake()->image('satu.jpg'),
                UploadedFile::fake()->image('dua.png'),
            ])
            ->assertCount('photos', 2)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.reports.index'));

        $report = DisasterReport::where('title', 'Banjir di Dayeuhkolot')->firstOrFail();

        $this->assertSame($this->warga->id, $report->reporter_id);
        $this->assertSame(DisasterReport::STATUS_PENDING, $report->status);
        $this->assertNull($report->estimated_affected);
        $this->assertCount(2, $report->photos);
        Storage::disk('public')->assertExists($report->photos->first()->path);
    }

    public function test_map_point_is_required(): void
    {
        Livewire::actingAs($this->warga)
            ->test(CreateReport::class)
            ->set('disaster_type', 'banjir')
            ->set('title', 'Banjir tanpa titik')
            ->set('description', 'Deskripsi')
            ->set('address', 'Alamat')
            ->call('save')
            ->assertHasErrors(['lat' => 'required', 'lng' => 'required']);

        $this->assertDatabaseCount('disaster_reports', 0);
    }

    public function test_photos_are_limited_to_three(): void
    {
        Livewire::actingAs($this->warga)
            ->test(CreateReport::class)
            ->set('newPhotos', [
                UploadedFile::fake()->image('1.jpg'),
                UploadedFile::fake()->image('2.jpg'),
            ])
            ->set('newPhotos', [
                UploadedFile::fake()->image('3.jpg'),
                UploadedFile::fake()->image('4.jpg'),
            ])
            ->assertCount('photos', 3)
            ->assertHasErrors('photos')
            ->call('removePhoto', 0)
            ->assertCount('photos', 2);
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Livewire::actingAs($this->warga)
            ->test(CreateReport::class)
            ->set('newPhotos', [UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf')])
            ->assertHasErrors('newPhotos.0')
            ->assertCount('photos', 0);
    }
}
