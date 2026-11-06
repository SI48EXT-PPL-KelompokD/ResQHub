<?php

namespace Tests\Feature;

use App\Http\Requests\DisasterReport\StoreDisasterReportRequest;
use App\Models\ActivityLog;
use App\Models\DisasterEvent;
use App\Models\DisasterReport;
use App\Models\User;
use App\Notifications\DisasterReportVerifiedNotification;
use App\Services\DisasterReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DisasterReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private DisasterReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Notification::fake();
        $this->service = app(DisasterReportService::class);
    }

    private function warga(): User
    {
        return User::factory()->create(['role' => 'warga']);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'disaster_type' => 'banjir',
            'title' => 'Banjir di RW 07',
            'description' => 'Air setinggi 1 meter merendam rumah warga.',
            'estimated_affected' => 50,
            'lat' => -6.995,
            'lng' => 107.624,
            'address' => 'Jl. Andir No. 45, Baleendah, Kab. Bandung',
            'occurred_at' => now()->subHours(2)->toDateTimeString(),
        ], $overrides);
    }

    public function test_warga_can_create_report_with_photos_and_pending_status(): void
    {
        $warga = $this->warga();
        $photos = [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')];

        $report = $this->service->create($warga, $this->payload(), $photos);

        $this->assertSame(DisasterReport::STATUS_PENDING, $report->status);
        $this->assertSame($warga->id, $report->reporter_id);
        $this->assertCount(2, $report->photos);
        Storage::disk('public')->assertExists($report->photos->first()->path);
        $this->assertDatabaseHas('activity_logs', ['subject_id' => $report->id, 'action' => 'laporan_dibuat']);
    }

    public function test_create_rejects_more_than_three_photos(): void
    {
        $photos = array_map(fn ($i) => UploadedFile::fake()->image("p{$i}.jpg"), range(1, 4));

        $this->expectException(ValidationException::class);
        $this->service->create($this->warga(), $this->payload(), $photos);
    }

    public function test_store_request_validates_required_fields_and_photo_rules(): void
    {
        $rules = (new StoreDisasterReportRequest)->rules();

        $this->assertTrue(Validator::make($this->payload(), $rules)->passes());

        $invalid = Validator::make([
            'disaster_type' => 'meteor',
            'occurred_at' => now()->addDay()->toDateTimeString(),
            'photos' => [UploadedFile::fake()->image('big.jpg')->size(3000)],
        ], $rules);

        $this->assertTrue($invalid->fails());
        foreach (['disaster_type', 'title', 'description', 'lat', 'lng', 'address', 'occurred_at', 'photos.0'] as $field) {
            $this->assertArrayHasKey($field, $invalid->errors()->toArray());
        }
    }

    public function test_reporter_list_only_contains_own_reports(): void
    {
        $warga = $this->warga();
        DisasterReport::factory()->count(2)->create(['reporter_id' => $warga->id]);
        DisasterReport::factory()->create();

        $this->assertSame(2, $this->service->listForReporter($warga)->total());
    }

    public function test_admin_list_filters_by_type_status_area_and_date(): void
    {
        DisasterReport::factory()->create(['disaster_type' => 'banjir', 'address' => 'Baleendah', 'occurred_at' => '2026-11-01 08:00:00']);
        DisasterReport::factory()->accepted()->create(['disaster_type' => 'banjir', 'address' => 'Baleendah']);
        DisasterReport::factory()->create(['disaster_type' => 'tanah_longsor', 'address' => 'Lembang']);

        $this->assertSame(2, $this->service->listForAdmin(['disaster_type' => 'banjir'])->total());
        $this->assertSame(2, $this->service->listForAdmin(['status' => DisasterReport::STATUS_PENDING])->total());
        $this->assertSame(1, $this->service->listForAdmin(['area' => 'Lembang'])->total());
        $this->assertSame(1, $this->service->listForAdmin(['date_from' => '2026-11-01', 'date_to' => '2026-11-01'])->total());
    }

    public function test_nearby_reports_returns_only_reports_within_five_km(): void
    {
        $report = DisasterReport::factory()->create(['lat' => -6.995, 'lng' => 107.624]);
        $near = DisasterReport::factory()->create(['lat' => -7.005, 'lng' => 107.630]);
        DisasterReport::factory()->create(['lat' => -6.900, 'lng' => 107.620]);

        $result = $this->service->nearbyReports($report);

        $this->assertSame([$near->id], $result->pluck('id')->all());
        $this->assertLessThan(5, $result->first()->distance_km);
    }

    public function test_reporter_can_update_pending_report_and_replace_photos(): void
    {
        $warga = $this->warga();
        $report = $this->service->create($warga, $this->payload(), [UploadedFile::fake()->image('a.jpg')]);
        $oldPhoto = $report->photos->first();

        $updated = $this->service->update(
            $warga,
            $report,
            ['title' => 'Banjir makin tinggi'],
            [UploadedFile::fake()->image('b.jpg')],
            [$oldPhoto->id],
        );

        $this->assertSame('Banjir makin tinggi', $updated->title);
        $this->assertCount(1, $updated->photos);
        Storage::disk('public')->assertMissing($oldPhoto->path);
    }

    public function test_verified_report_cannot_be_updated_or_deleted(): void
    {
        $warga = $this->warga();
        $report = DisasterReport::factory()->accepted()->create(['reporter_id' => $warga->id]);

        try {
            $this->service->update($warga, $report, ['title' => 'Ubah']);
            $this->fail('Laporan terverifikasi seharusnya tidak dapat diubah.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->expectException(HttpException::class);
        $this->service->delete($warga, $report);
    }

    public function test_other_warga_cannot_update_report(): void
    {
        $report = DisasterReport::factory()->create();

        $this->expectException(HttpException::class);
        $this->service->update($this->warga(), $report, ['title' => 'Bukan milik saya']);
    }

    public function test_admin_accepts_report_into_new_event_and_notifies_reporter(): void
    {
        $report = DisasterReport::factory()->create();

        $accepted = $this->service->accept($this->admin(), $report, null, [
            'name' => 'Banjir Citarum November 2026',
            'severity' => 'sedang',
            'regency' => 'Kabupaten Bandung',
        ]);

        $this->assertSame(DisasterReport::STATUS_ACCEPTED, $accepted->status);
        $this->assertNotNull($accepted->verified_at);
        $this->assertDatabaseHas('disaster_events', ['id' => $accepted->disaster_event_id, 'name' => 'Banjir Citarum November 2026']);
        Notification::assertSentTo($report->reporter, DisasterReportVerifiedNotification::class);
    }

    public function test_admin_accepts_report_into_existing_event(): void
    {
        $event = DisasterEvent::factory()->create();
        $report = DisasterReport::factory()->create();

        $accepted = $this->service->accept($this->admin(), $report, $event->id);

        $this->assertSame($event->id, $accepted->disaster_event_id);
        $this->assertSame(1, DisasterEvent::count());
    }

    public function test_accept_requires_event_choice(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->accept($this->admin(), DisasterReport::factory()->create());
    }

    public function test_admin_rejects_report_with_reason(): void
    {
        $report = DisasterReport::factory()->create();

        $rejected = $this->service->reject($this->admin(), $report, 'Lokasi tidak sesuai foto.');

        $this->assertSame(DisasterReport::STATUS_REJECTED, $rejected->status);
        $this->assertSame('Lokasi tidak sesuai foto.', $rejected->reject_reason);
        Notification::assertSentTo($report->reporter, DisasterReportVerifiedNotification::class);
    }

    public function test_reject_requires_reason(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->reject($this->admin(), DisasterReport::factory()->create(), '   ');
    }

    public function test_non_admin_cannot_verify_and_verified_report_cannot_be_verified_again(): void
    {
        $report = DisasterReport::factory()->create();

        try {
            $this->service->reject($this->warga(), $report, 'Bukan admin');
            $this->fail('Warga seharusnya tidak dapat memverifikasi laporan.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $admin = $this->admin();
        $this->service->reject($admin, $report, 'Duplikat');

        $this->expectException(HttpException::class);
        $this->service->reject($admin, $report->fresh(), 'Ditolak lagi');
    }

    public function test_reporter_can_delete_pending_report_and_its_photos(): void
    {
        $warga = $this->warga();
        $report = $this->service->create($warga, $this->payload(), [UploadedFile::fake()->image('a.jpg')]);
        $path = $report->photos->first()->path;

        $this->service->delete($warga, $report);

        $this->assertDatabaseMissing('disaster_reports', ['id' => $report->id]);
        $this->assertDatabaseMissing('report_photos', ['path' => $path]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_history_returns_status_changes_in_order(): void
    {
        $warga = $this->warga();
        $report = $this->service->create($warga, $this->payload());
        $this->service->reject($this->admin(), $report, 'Tidak valid');

        $this->assertSame(['laporan_dibuat', 'laporan_ditolak'], $this->service->history($report)->pluck('action')->all());
        $this->assertSame(2, ActivityLog::count());
    }
}
