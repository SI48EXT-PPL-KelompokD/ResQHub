<?php

namespace Tests\Feature;

use App\Livewire\Report\VerificationQueue;
use App\Livewire\Report\VerifyReport;
use App\Models\ActivityLog;
use App\Models\DisasterEvent;
use App\Models\DisasterReport;
use App\Models\User;
use App\Notifications\DisasterReportVerifiedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DisasterReportAdminVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $warga;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->warga = User::factory()->create(['role' => 'warga']);
    }

    private function makeReport(array $overrides = []): DisasterReport
    {
        return DisasterReport::factory()->create(array_merge([
            'reporter_id' => $this->warga->id,
            'title' => 'Banjir di Baleendah',
            'address' => 'Jl. Raya Baleendah',
        ], $overrides));
    }

    public function test_admin_can_open_queue_and_verification_pages(): void
    {
        $report = $this->makeReport();

        $this->actingAs($this->admin)->get(route('admin.reports.verification'))
            ->assertOk()
            ->assertSee('Verifikasi Laporan Kejadian')
            ->assertSee('Peta Sebaran Laporan')
            ->assertSee('Banjir di Baleendah')
            ->assertSee('1 menunggu verifikasi');

        $this->actingAs($this->admin)->get(route('admin.reports.verify', $report))
            ->assertOk()
            ->assertSee('Banjir di Baleendah')
            ->assertSee('Keputusan Verifikasi')
            ->assertSee('Riwayat Status')
            ->assertSee($this->warga->name);
    }

    public function test_non_admin_cannot_open_verification_pages(): void
    {
        $report = $this->makeReport();
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        foreach ([$this->warga, $koordinator] as $user) {
            $this->actingAs($user)->get(route('admin.reports.verification'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.reports.verify', $report))->assertForbidden();
        }

        Livewire::actingAs($this->warga)->test(VerificationQueue::class)->assertForbidden();
        Livewire::actingAs($this->warga)->test(VerifyReport::class, ['report' => $report])->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $report = $this->makeReport();

        $this->get(route('admin.reports.verification'))->assertRedirect(route('login'));
        $this->get(route('admin.reports.verify', $report))->assertRedirect(route('login'));
    }

    public function test_admin_sidebar_links_to_verification_queue(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.reports.verification'), false);
    }

    public function test_queue_shows_pending_reports_first(): void
    {
        $this->makeReport(['title' => 'Laporan lama diterima', 'status' => DisasterReport::STATUS_ACCEPTED, 'created_at' => now()]);
        $this->makeReport(['title' => 'Laporan masih menunggu', 'created_at' => now()->subDay()]);

        Livewire::actingAs($this->admin)
            ->test(VerificationQueue::class)
            ->assertSeeInOrder(['Laporan masih menunggu', 'Laporan lama diterima']);
    }

    public function test_queue_filters_by_type_status_area_and_date(): void
    {
        $this->makeReport(['title' => 'Banjir Dayeuhkolot', 'address' => 'Dayeuhkolot', 'occurred_at' => '2026-11-10 08:00:00']);
        $this->makeReport(['title' => 'Longsor Pangalengan', 'disaster_type' => 'tanah_longsor', 'address' => 'Pangalengan', 'occurred_at' => '2026-11-12 08:00:00']);
        $this->makeReport(['title' => 'Gempa Ciwidey', 'disaster_type' => 'gempa_bumi', 'address' => 'Ciwidey', 'status' => DisasterReport::STATUS_REJECTED, 'reject_reason' => 'Tidak valid', 'occurred_at' => '2026-11-14 08:00:00']);

        Livewire::actingAs($this->admin)
            ->test(VerificationQueue::class)
            ->assertSee(['Banjir Dayeuhkolot', 'Longsor Pangalengan', 'Gempa Ciwidey'])
            ->set('type', 'tanah_longsor')
            ->assertSee('Longsor Pangalengan')
            ->assertDontSee('Banjir Dayeuhkolot')
            ->assertDontSee('Gempa Ciwidey')
            ->assertDispatched('report-points-updated')
            ->set('type', '')
            ->set('status', DisasterReport::STATUS_REJECTED)
            ->assertSee('Gempa Ciwidey')
            ->assertDontSee('Banjir Dayeuhkolot')
            ->set('status', '')
            ->set('area', 'Dayeuh')
            ->assertSee('Banjir Dayeuhkolot')
            ->assertDontSee('Longsor Pangalengan')
            ->set('area', '')
            ->set('dateFrom', '2026-11-11')
            ->set('dateTo', '2026-11-13')
            ->assertSee('Longsor Pangalengan')
            ->assertDontSee('Banjir Dayeuhkolot')
            ->assertDontSee('Gempa Ciwidey')
            ->call('resetFilters')
            ->assertSet('dateFrom', '')
            ->assertSee(['Banjir Dayeuhkolot', 'Longsor Pangalengan', 'Gempa Ciwidey']);
    }

    public function test_queue_is_paginated_and_map_points_cover_all_filtered_reports(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->makeReport(['title' => 'Laporan nomor '.str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'created_at' => now()->subMinutes($i)]);
        }

        Livewire::actingAs($this->admin)
            ->test(VerificationQueue::class)
            ->assertViewHas('reports', fn ($reports) => $reports->total() === 12
                && $reports->count() === 10
                && $reports->first()->title === 'Laporan nomor 01')
            ->assertViewHas('points', fn ($points) => $points->count() === 12)
            ->call('gotoPage', 2)
            ->assertViewHas('reports', fn ($reports) => $reports->count() === 2
                && $reports->pluck('title')->all() === ['Laporan nomor 11', 'Laporan nomor 12']);
    }

    public function test_verification_page_lists_other_reports_within_five_km(): void
    {
        $report = $this->makeReport(['lat' => -6.99, 'lng' => 107.62]);
        $this->makeReport(['title' => 'Laporan dekat sungai', 'lat' => -6.999, 'lng' => 107.62]);
        $this->makeReport(['title' => 'Laporan jauh di kota lain', 'lat' => -7.08, 'lng' => 107.62]);

        Livewire::actingAs($this->admin)
            ->test(VerifyReport::class, ['report' => $report])
            ->assertSee('Laporan Lain dalam Radius 5 km')
            ->assertSee('Laporan dekat sungai')
            ->assertSee('1,00 km')
            ->assertDontSee('Laporan jauh di kota lain');
    }

    public function test_admin_accepts_report_into_existing_event(): void
    {
        $report = $this->makeReport();
        $event = DisasterEvent::factory()->create(['name' => 'Banjir Bandung Selatan']);

        Livewire::actingAs($this->admin)
            ->test(VerifyReport::class, ['report' => $report])
            ->assertSet('eventMode', 'existing')
            ->assertSee('Banjir Bandung Selatan')
            ->set('disaster_event_id', (string) $event->id)
            ->call('accept')
            ->assertHasNoErrors()
            ->assertSee('Laporan diterima dan dikaitkan ke kejadian bencana')
            ->assertDontSee('Keputusan Verifikasi');

        $report->refresh();

        $this->assertSame(DisasterReport::STATUS_ACCEPTED, $report->status);
        $this->assertSame($event->id, $report->disaster_event_id);
        $this->assertSame($this->admin->id, $report->verified_by);
        $this->assertSame(1, DisasterEvent::count());
    }

    public function test_admin_accepts_report_into_new_event(): void
    {
        $report = $this->makeReport(['disaster_type' => 'tanah_longsor', 'lat' => -7.1234, 'lng' => 107.5678]);

        Livewire::actingAs($this->admin)
            ->test(VerifyReport::class, ['report' => $report])
            ->assertSet('eventMode', 'new')
            ->set('new_event_name', 'Longsor Pangalengan November 2026')
            ->set('new_event_severity', 'berat')
            ->set('new_event_regency', 'Kabupaten Bandung')
            ->call('accept')
            ->assertHasNoErrors();

        $event = DisasterEvent::firstOrFail();
        $report->refresh();

        $this->assertSame('Longsor Pangalengan November 2026', $event->name);
        $this->assertSame('tanah_longsor', $event->disaster_type);
        $this->assertSame('berat', $event->severity);
        $this->assertSame(-7.1234, $event->lat);
        $this->assertSame(107.5678, $event->lng);
        $this->assertSame($event->id, $report->disaster_event_id);
        $this->assertSame(DisasterReport::STATUS_ACCEPTED, $report->status);
        $this->assertTrue(ActivityLog::where('action', 'laporan_diterima')->where('subject_id', $report->id)->exists());
        $this->assertSame(1, $this->warga->notifications()->where('type', DisasterReportVerifiedNotification::class)->count());
    }

    public function test_accept_requires_event_choice_or_complete_new_event(): void
    {
        $report = $this->makeReport();
        DisasterEvent::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(VerifyReport::class, ['report' => $report])
            ->call('accept')
            ->assertHasErrors(['disaster_event_id' => 'required'])
            ->set('disaster_event_id', '9999')
            ->call('accept')
            ->assertHasErrors(['disaster_event_id' => 'exists'])
            ->set('eventMode', 'new')
            ->set('new_event_name', 'Banjir Baru')
            ->call('accept')
            ->assertHasErrors(['new_event_severity' => 'required', 'new_event_regency' => 'required']);

        $this->assertTrue($report->fresh()->isPending());
        $this->assertSame(1, DisasterEvent::count());
    }

    public function test_reject_requires_reason(): void
    {
        $report = $this->makeReport();

        Livewire::actingAs($this->admin)
            ->test(VerifyReport::class, ['report' => $report])
            ->set('reject_reason', '   ')
            ->call('reject')
            ->assertHasErrors(['reject_reason' => 'required'])
            ->assertSee('Alasan penolakan wajib diisi.');

        $this->assertTrue($report->fresh()->isPending());
    }

    public function test_admin_rejects_report_with_reason_and_reporter_is_notified(): void
    {
        $report = $this->makeReport();

        Livewire::actingAs($this->admin)
            ->test(VerifyReport::class, ['report' => $report])
            ->set('reject_reason', 'Foto tidak sesuai lokasi kejadian.')
            ->call('reject')
            ->assertHasNoErrors()
            ->assertSee('Laporan ditolak')
            ->assertSee('Foto tidak sesuai lokasi kejadian.')
            ->assertDontSee('Keputusan Verifikasi');

        $report->refresh();

        $this->assertSame(DisasterReport::STATUS_REJECTED, $report->status);
        $this->assertSame('Foto tidak sesuai lokasi kejadian.', $report->reject_reason);
        $this->assertSame($this->admin->id, $report->verified_by);

        $notification = $this->warga->notifications()->firstOrFail();
        $this->assertSame(DisasterReport::STATUS_REJECTED, $notification->data['status']);
        $this->assertTrue(ActivityLog::where('action', 'laporan_ditolak')->where('subject_id', $report->id)->exists());
    }

    public function test_verified_report_has_no_decision_form_and_cannot_be_verified_again(): void
    {
        $event = DisasterEvent::factory()->create();
        $report = $this->makeReport([
            'status' => DisasterReport::STATUS_ACCEPTED,
            'disaster_event_id' => $event->id,
            'verified_by' => $this->admin->id,
            'verified_at' => now(),
        ]);

        $this->actingAs($this->admin)->get(route('admin.reports.verify', $report))
            ->assertOk()
            ->assertDontSee('Keputusan Verifikasi')
            ->assertSee('Diterima oleh '.$this->admin->name);

        Livewire::actingAs($this->admin)
            ->test(VerifyReport::class, ['report' => $report])
            ->set('reject_reason', 'Mencoba menolak ulang')
            ->call('reject')
            ->assertForbidden();

        $this->assertSame(DisasterReport::STATUS_ACCEPTED, $report->fresh()->status);
        $this->assertSame(0, $this->warga->notifications()->count());
    }
}
