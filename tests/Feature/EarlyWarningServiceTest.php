<?php

namespace Tests\Feature;

use App\Http\Requests\EarlyWarning\StoreEarlyWarningRequest;
use App\Models\EarlyWarning;
use App\Models\User;
use App\Notifications\EarlyWarningNotification;
use App\Services\EarlyWarningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EarlyWarningServiceTest extends TestCase
{
    use RefreshDatabase;

    private EarlyWarningService $service;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->service = app(EarlyWarningService::class);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function wargaIn(string $regency): User
    {
        return User::factory()->create(['role' => 'warga', 'regency' => $regency]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Kenaikan muka air Sungai Citarum',
            'hazard_type' => 'banjir',
            'level' => 'waspada',
            'target_regencies' => ['Kabupaten Bandung'],
            'center_lat' => -6.99,
            'center_lng' => 107.62,
            'radius_km' => 10,
            'instruction' => 'Siapkan tas siaga dan menuju titik kumpul bila air naik.',
            'starts_at' => now()->subHour()->toDateTimeString(),
            'ends_at' => now()->addDay()->toDateTimeString(),
        ], $overrides);
    }

    public function test_store_request_validates_target_area_and_period(): void
    {
        $rules = (new StoreEarlyWarningRequest)->rules();

        $this->assertTrue(Validator::make($this->payload(), $rules)->passes());
        $this->assertTrue(Validator::make($this->payload(['target_regencies' => null]), $rules)->passes());

        $invalid = Validator::make($this->payload([
            'level' => 'bahaya',
            'target_regencies' => null,
            'radius_km' => null,
            'ends_at' => now()->subDay()->toDateTimeString(),
        ]), $rules);

        $this->assertTrue($invalid->fails());
        foreach (['level', 'target_regencies', 'radius_km', 'ends_at'] as $field) {
            $this->assertArrayHasKey($field, $invalid->errors()->toArray());
        }
    }

    public function test_admin_can_save_draft_without_notifying(): void
    {
        $this->wargaIn('Kabupaten Bandung');

        $warning = $this->service->create($this->admin, $this->payload());

        $this->assertSame(EarlyWarning::STATUS_DRAFT, $warning->status);
        Notification::assertNothingSent();
    }

    public function test_publishing_notifies_only_warga_in_target_regency(): void
    {
        $target = $this->wargaIn('Kabupaten Bandung');
        $outside = $this->wargaIn('Kota Bogor');
        $koordinator = User::factory()->create(['role' => 'koordinator', 'regency' => 'Kabupaten Bandung']);

        $warning = $this->service->create($this->admin, $this->payload(), publish: true);

        $this->assertSame(EarlyWarning::STATUS_ACTIVE, $warning->status);
        $this->assertSame(1, $this->service->recipientCount($warning));
        Notification::assertSentTo($target, EarlyWarningNotification::class,
            fn ($n) => $n->event === EarlyWarningNotification::EVENT_ISSUED);
        Notification::assertNotSentTo([$outside, $koordinator], EarlyWarningNotification::class);
    }

    public function test_publish_draft_later(): void
    {
        $target = $this->wargaIn('Kabupaten Bandung');
        $warning = $this->service->create($this->admin, $this->payload());

        $this->service->publish($this->admin, $warning);

        $this->assertTrue($warning->fresh()->isActive());
        Notification::assertSentTo($target, EarlyWarningNotification::class);
    }

    public function test_non_admin_cannot_manage_warnings(): void
    {
        $this->expectException(HttpException::class);
        $this->service->create($this->wargaIn('Kabupaten Bandung'), $this->payload());
    }

    public function test_admin_list_filters_by_status_and_level(): void
    {
        EarlyWarning::factory()->create(['level' => 'awas']);
        EarlyWarning::factory()->active()->create(['level' => 'siaga']);
        EarlyWarning::factory()->active()->create(['level' => 'awas']);

        $this->assertSame(2, $this->service->listForAdmin($this->admin, ['status' => 'aktif'])->total());
        $this->assertSame(2, $this->service->listForAdmin($this->admin, ['level' => 'awas'])->total());
    }

    public function test_public_banner_shows_only_currently_active_sorted_by_level(): void
    {
        $siaga = EarlyWarning::factory()->active()->create(['level' => 'siaga']);
        $awas = EarlyWarning::factory()->active()->create(['level' => 'awas']);
        EarlyWarning::factory()->create(['level' => 'awas']);
        EarlyWarning::factory()->active()->create(['ends_at' => now()->subMinute()]);
        EarlyWarning::factory()->active()->create(['target_regencies' => ['Kota Bogor']]);

        $this->assertSame([$awas->id, $siaga->id], $this->service->activeForPublic('Kabupaten Bandung')->pluck('id')->all());
    }

    public function test_update_records_history_and_escalation_renotifies(): void
    {
        $target = $this->wargaIn('Kabupaten Bandung');
        $warning = $this->service->create($this->admin, $this->payload(), publish: true);

        $this->service->update($this->admin, $warning, ['level' => 'awas'], 'Debit air terus naik');

        $history = $this->service->history($warning);
        $this->assertCount(1, $history);
        $this->assertSame(['waspada', 'awas', 'Debit air terus naik'], [$history[0]->old_level, $history[0]->new_level, $history[0]->note]);
        Notification::assertSentTo($target, EarlyWarningNotification::class,
            fn ($n) => $n->event === EarlyWarningNotification::EVENT_ESCALATED);
    }

    public function test_lowering_level_does_not_renotify(): void
    {
        $target = $this->wargaIn('Kabupaten Bandung');
        $warning = $this->service->create($this->admin, $this->payload(['level' => 'awas']), publish: true);

        $this->service->update($this->admin, $warning, ['level' => 'siaga']);

        Notification::assertSentToTimes($target, EarlyWarningNotification::class, 1);
    }

    public function test_draft_can_be_deleted_but_active_warning_cannot(): void
    {
        $draft = $this->service->create($this->admin, $this->payload());
        $this->service->deleteDraft($this->admin, $draft);
        $this->assertDatabaseMissing('early_warnings', ['id' => $draft->id]);

        $active = $this->service->create($this->admin, $this->payload(), publish: true);
        $this->expectException(HttpException::class);
        $this->service->deleteDraft($this->admin, $active);
    }

    public function test_revoke_requires_reason_and_notifies(): void
    {
        $target = $this->wargaIn('Kabupaten Bandung');
        $warning = $this->service->create($this->admin, $this->payload(), publish: true);

        try {
            $this->service->revoke($this->admin, $warning, ' ');
            $this->fail('Pencabutan tanpa alasan seharusnya ditolak.');
        } catch (ValidationException) {
            $this->assertTrue($warning->fresh()->isActive());
        }

        $this->service->revoke($this->admin, $warning, 'Debit sungai sudah normal');

        $this->assertSame(EarlyWarning::STATUS_REVOKED, $warning->fresh()->status);
        $this->assertCount(0, $this->service->activeForPublic());
        Notification::assertSentTo($target, EarlyWarningNotification::class,
            fn ($n) => $n->event === EarlyWarningNotification::EVENT_REVOKED);
    }

    public function test_expired_warning_cannot_be_updated(): void
    {
        $warning = EarlyWarning::factory()->create(['status' => EarlyWarning::STATUS_EXPIRED]);

        $this->expectException(HttpException::class);
        $this->service->update($this->admin, $warning, ['level' => 'awas']);
    }

    public function test_expire_command_marks_overdue_active_warnings(): void
    {
        $overdue = EarlyWarning::factory()->active()->create(['ends_at' => now()->subMinute()]);
        $current = EarlyWarning::factory()->active()->create();
        $draft = EarlyWarning::factory()->create(['ends_at' => now()->subMinute()]);

        $this->artisan('warnings:expire')->assertSuccessful();

        $this->assertSame(EarlyWarning::STATUS_EXPIRED, $overdue->fresh()->status);
        $this->assertSame(EarlyWarning::STATUS_ACTIVE, $current->fresh()->status);
        $this->assertSame(EarlyWarning::STATUS_DRAFT, $draft->fresh()->status);
    }
}
