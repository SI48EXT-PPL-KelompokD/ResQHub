<?php

namespace Tests\Feature;

use App\Livewire\Warning\ShowWarning;
use App\Livewire\Warning\WarningForm;
use App\Livewire\Warning\WarningList;
use App\Models\EarlyWarning;
use App\Models\User;
use App\Models\WarningUpdate;
use App\Notifications\EarlyWarningNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class EarlyWarningAdminPageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Notification::fake();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function makeWarning(array $overrides = []): EarlyWarning
    {
        return EarlyWarning::factory()->create(array_merge([
            'issued_by' => $this->admin->id,
            'title' => 'Potensi Banjir Citarum',
        ], $overrides));
    }

    private function fillForm($component)
    {
        return $component
            ->set('title', 'Potensi Banjir Dayeuhkolot')
            ->set('hazard_type', 'banjir')
            ->set('level', 'siaga')
            ->set('target_regencies', ['Kabupaten Bandung'])
            ->set('instruction', 'Siapkan tas siaga dan menuju posko terdekat bila air naik.')
            ->set('starts_at', now()->format('Y-m-d\TH:i'))
            ->set('ends_at', now()->addDay()->format('Y-m-d\TH:i'));
    }

    public function test_admin_can_open_list_create_detail_and_edit_pages(): void
    {
        $warning = $this->makeWarning();

        $this->actingAs($this->admin)->get(route('admin.warnings.index'))
            ->assertOk()
            ->assertSee('Peringatan Dini')
            ->assertSee('Potensi Banjir Citarum');

        $this->actingAs($this->admin)->get(route('admin.warnings.create'))
            ->assertOk()
            ->assertSee('Buat Peringatan Dini')
            ->assertSee('Simpan sebagai Draf');

        $this->actingAs($this->admin)->get(route('admin.warnings.show', $warning))
            ->assertOk()
            ->assertSee('Warga Penerima Notifikasi')
            ->assertSee('Riwayat Pembaruan');

        $this->actingAs($this->admin)->get(route('admin.warnings.edit', $warning))
            ->assertOk()
            ->assertSee('Ubah Peringatan Dini');
    }

    public function test_non_admin_cannot_open_warning_pages(): void
    {
        $warga = User::factory()->create(['role' => 'warga']);
        $warning = $this->makeWarning();

        $this->actingAs($warga)->get(route('admin.warnings.index'))->assertForbidden();
        $this->actingAs($warga)->get(route('admin.warnings.create'))->assertForbidden();
        $this->actingAs($warga)->get(route('admin.warnings.show', $warning))->assertForbidden();
    }

    public function test_list_filters_by_status_and_level(): void
    {
        $this->makeWarning(['title' => 'Draf Waspada']);
        $this->makeWarning(['title' => 'Aktif Awas', 'status' => EarlyWarning::STATUS_ACTIVE, 'level' => 'awas']);

        Livewire::actingAs($this->admin)->test(WarningList::class)
            ->assertSee('Draf Waspada')
            ->assertSee('Aktif Awas')
            ->set('status', EarlyWarning::STATUS_ACTIVE)
            ->assertSee('Aktif Awas')
            ->assertDontSee('Draf Waspada')
            ->set('status', '')
            ->set('level', 'waspada')
            ->assertSee('Draf Waspada')
            ->assertDontSee('Aktif Awas');
    }

    public function test_admin_can_save_draft_without_notifying_citizens(): void
    {
        User::factory()->create(['role' => 'warga', 'regency' => 'Kabupaten Bandung']);

        $this->fillForm(Livewire::actingAs($this->admin)->test(WarningForm::class))
            ->call('saveDraft')
            ->assertHasNoErrors()
            ->assertRedirect();

        $warning = EarlyWarning::firstOrFail();
        $this->assertSame(EarlyWarning::STATUS_DRAFT, $warning->status);
        $this->assertNull($warning->radius_km);
        $this->assertNull($warning->center_lat);
        Notification::assertNothingSent();
    }

    public function test_admin_can_publish_directly_and_citizens_in_target_area_are_notified(): void
    {
        $inside = User::factory()->create(['role' => 'warga', 'regency' => 'Kabupaten Bandung']);
        $outside = User::factory()->create(['role' => 'warga', 'regency' => 'Kota Cimahi']);

        $this->fillForm(Livewire::actingAs($this->admin)->test(WarningForm::class))
            ->call('publish')
            ->assertHasNoErrors();

        $this->assertSame(EarlyWarning::STATUS_ACTIVE, EarlyWarning::firstOrFail()->status);
        Notification::assertSentTo($inside, EarlyWarningNotification::class);
        Notification::assertNotSentTo($outside, EarlyWarningNotification::class);
    }

    public function test_radius_only_target_is_accepted(): void
    {
        $this->fillForm(Livewire::actingAs($this->admin)->test(WarningForm::class))
            ->set('target_regencies', [])
            ->set('useRadius', true)
            ->set('center_lat', -6.99)
            ->set('center_lng', 107.62)
            ->set('radius_km', 15)
            ->call('saveDraft')
            ->assertHasNoErrors();

        $warning = EarlyWarning::firstOrFail();
        $this->assertNull($warning->target_regencies);
        $this->assertSame(15, $warning->radius_km);
    }

    public function test_form_requires_target_area_and_valid_period(): void
    {
        $this->fillForm(Livewire::actingAs($this->admin)->test(WarningForm::class))
            ->set('target_regencies', [])
            ->set('ends_at', now()->subDay()->format('Y-m-d\TH:i'))
            ->call('saveDraft')
            ->assertHasErrors(['target_regencies', 'ends_at']);

        $this->fillForm(Livewire::actingAs($this->admin)->test(WarningForm::class))
            ->set('useRadius', true)
            ->set('radius_km', 10)
            ->call('saveDraft')
            ->assertHasErrors(['center_lat']);

        $this->assertSame(0, EarlyWarning::count());
    }

    public function test_escalating_active_warning_records_history_and_renotifies(): void
    {
        $warga = User::factory()->create(['role' => 'warga', 'regency' => 'Kabupaten Bandung']);
        $warning = $this->makeWarning(['status' => EarlyWarning::STATUS_ACTIVE, 'level' => 'waspada']);

        Livewire::actingAs($this->admin)->test(WarningForm::class, ['warning' => $warning])
            ->assertSet('title', 'Potensi Banjir Citarum')
            ->set('level', 'awas')
            ->set('note', 'Debit air terus naik.')
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.warnings.show', $warning));

        $this->assertSame('awas', $warning->fresh()->level);
        $this->assertDatabaseHas('warning_updates', [
            'early_warning_id' => $warning->id,
            'old_level' => 'waspada',
            'new_level' => 'awas',
            'note' => 'Debit air terus naik.',
        ]);
        Notification::assertSentTo($warga, EarlyWarningNotification::class, fn ($n) => $n->event === EarlyWarningNotification::EVENT_ESCALATED);

        Livewire::actingAs($this->admin)->test(ShowWarning::class, ['warning' => $warning])
            ->assertSee('Tingkat dinaikkan')
            ->assertSee('Debit air terus naik.');
    }

    public function test_revoked_or_expired_warning_cannot_be_edited(): void
    {
        $revoked = $this->makeWarning(['status' => EarlyWarning::STATUS_REVOKED, 'revoke_reason' => 'Situasi normal']);
        $expired = $this->makeWarning(['status' => EarlyWarning::STATUS_EXPIRED]);

        $this->actingAs($this->admin)->get(route('admin.warnings.edit', $revoked))->assertForbidden();
        $this->actingAs($this->admin)->get(route('admin.warnings.edit', $expired))->assertForbidden();

        $this->actingAs($this->admin)->get(route('admin.warnings.show', $revoked))
            ->assertOk()
            ->assertSee('Situasi normal')
            ->assertDontSee('Cabut Peringatan');
    }

    public function test_detail_shows_recipient_count_and_admin_can_publish_draft(): void
    {
        User::factory()->count(2)->create(['role' => 'warga', 'regency' => 'Kabupaten Bandung']);
        User::factory()->create(['role' => 'warga', 'regency' => 'Kota Cimahi']);
        $warning = $this->makeWarning();

        Livewire::actingAs($this->admin)->test(ShowWarning::class, ['warning' => $warning])
            ->assertViewHas('recipientCount', 2)
            ->assertSee('Terbitkan')
            ->call('publish')
            ->assertSee('Peringatan diterbitkan');

        $this->assertSame(EarlyWarning::STATUS_ACTIVE, $warning->fresh()->status);
        Notification::assertCount(2);
    }

    public function test_admin_can_delete_draft_permanently(): void
    {
        $warning = $this->makeWarning();
        WarningUpdate::create([
            'early_warning_id' => $warning->id,
            'old_level' => 'waspada',
            'new_level' => 'waspada',
            'updated_by' => $this->admin->id,
        ]);

        Livewire::actingAs($this->admin)->test(ShowWarning::class, ['warning' => $warning])
            ->call('deleteDraft')
            ->assertRedirect(route('admin.warnings.index'));

        $this->assertModelMissing($warning);
    }

    public function test_active_warning_cannot_be_deleted_but_can_be_revoked_with_reason(): void
    {
        $warga = User::factory()->create(['role' => 'warga', 'regency' => 'Kabupaten Bandung']);
        $warning = $this->makeWarning(['status' => EarlyWarning::STATUS_ACTIVE]);

        Livewire::actingAs($this->admin)->test(ShowWarning::class, ['warning' => $warning])
            ->call('deleteDraft')
            ->assertForbidden();

        Livewire::actingAs($this->admin)->test(ShowWarning::class, ['warning' => $warning])
            ->assertSee('Cabut Peringatan')
            ->set('revoke_reason', '   ')
            ->call('revoke')
            ->assertHasErrors(['revoke_reason' => 'required'])
            ->set('revoke_reason', 'Debit air sudah kembali normal.')
            ->call('revoke')
            ->assertHasNoErrors()
            ->assertSee('Peringatan dicabut');

        $warning->refresh();
        $this->assertSame(EarlyWarning::STATUS_REVOKED, $warning->status);
        $this->assertSame('Debit air sudah kembali normal.', $warning->revoke_reason);
        Notification::assertSentTo($warga, EarlyWarningNotification::class, fn ($n) => $n->event === EarlyWarningNotification::EVENT_REVOKED);
    }

    public function test_admin_sidebar_links_to_warning_page(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.warnings.index'), false);
    }
}
