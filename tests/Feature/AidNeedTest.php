<?php

namespace Tests\Feature;

use App\Models\AidNeed;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AidNeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_aid_need_factory_creates_a_valid_open_need(): void
    {
        $coordinator = $this->coordinator();
        $shelterId = $this->shelter($coordinator);
        $item = $this->item('Beras Factory');

        $need = AidNeed::factory()->create([
            'shelter_id' => $shelterId,
            'item_id' => $item->id,
        ]);

        $this->assertDatabaseHas('aid_needs', [
            'id' => $need->id,
            'shelter_id' => $shelterId,
            'item_id' => $item->id,
            'status' => 'terbuka',
            'qty_pledged' => 0,
            'qty_fulfilled' => 0,
        ]);
        $this->assertTrue($need->item->is($item));
    }

    public function test_coordinator_can_create_a_need_for_owned_shelter(): void
    {
        $coordinator = $this->coordinator();
        $shelterId = $this->shelter($coordinator);
        $item = $this->item('Air Mineral');

        $response = $this->actingAs($coordinator)->post(route('admin.needs.store'), [
            'shelter_id' => $shelterId,
            'item_id' => $item->id,
            'qty_needed' => 100,
            'priority' => 'tinggi',
            'deadline' => now()->addDays(3)->toDateString(),
        ]);

        $response->assertRedirect(route('admin.needs.index'))->assertSessionHas('success');
        $this->assertDatabaseHas('aid_needs', [
            'shelter_id' => $shelterId,
            'item_id' => $item->id,
            'qty_needed' => 100,
            'priority' => 'tinggi',
            'status' => 'terbuka',
        ]);
    }

    public function test_urgent_need_requires_a_deadline(): void
    {
        $coordinator = $this->coordinator();
        $shelterId = $this->shelter($coordinator);
        $item = $this->item('Popok Bayi');

        $response = $this->actingAs($coordinator)
            ->from(route('admin.needs.create'))
            ->post(route('admin.needs.store'), [
                'shelter_id' => $shelterId,
                'item_id' => $item->id,
                'qty_needed' => 30,
                'priority' => 'mendesak',
            ]);

        $response->assertRedirect(route('admin.needs.create'))->assertSessionHasErrors('deadline');
        $this->assertDatabaseMissing('aid_needs', ['shelter_id' => $shelterId, 'item_id' => $item->id]);
    }

    public function test_duplicate_open_need_for_same_shelter_and_item_is_rejected(): void
    {
        $coordinator = $this->coordinator();
        $shelterId = $this->shelter($coordinator);
        $item = $this->item('Selimut');
        AidNeed::create([
            'shelter_id' => $shelterId,
            'item_id' => $item->id,
            'qty_needed' => 20,
            'priority' => 'normal',
            'status' => 'terbuka',
        ]);

        $response = $this->actingAs($coordinator)
            ->from(route('admin.needs.create'))
            ->post(route('admin.needs.store'), [
                'shelter_id' => $shelterId,
                'item_id' => $item->id,
                'qty_needed' => 40,
                'priority' => 'normal',
            ]);

        $response->assertRedirect(route('admin.needs.create'))->assertSessionHasErrors('item_id');
        $this->assertDatabaseCount('aid_needs', 1);
    }

    public function test_coordinator_cannot_manage_a_need_from_another_shelter(): void
    {
        $owner = $this->coordinator('Budi');
        $otherCoordinator = $this->coordinator('Siti');
        $shelterId = $this->shelter($owner);
        $item = $this->item('Air Bersih');
        $need = AidNeed::create([
            'shelter_id' => $shelterId,
            'item_id' => $item->id,
            'qty_needed' => 20,
            'priority' => 'normal',
            'status' => 'terbuka',
        ]);

        $this->actingAs($otherCoordinator)->get(route('admin.needs.edit', $need))->assertForbidden();
        $this->actingAs($otherCoordinator)->put(route('admin.needs.update', $need), [
            'qty_needed' => 30,
            'priority' => 'normal',
        ])->assertForbidden();
        $this->actingAs($otherCoordinator)->delete(route('admin.needs.destroy', $need), [
            'close_reason' => 'Tidak berwenang',
        ])->assertForbidden();
    }

    public function test_quantity_cannot_be_reduced_below_pledged_and_fulfilled_amount(): void
    {
        $coordinator = $this->coordinator();
        $shelterId = $this->shelter($coordinator);
        $item = $this->item('Beras Edit');
        $need = AidNeed::create([
            'shelter_id' => $shelterId,
            'item_id' => $item->id,
            'qty_needed' => 20,
            'qty_pledged' => 5,
            'qty_fulfilled' => 4,
            'priority' => 'normal',
            'status' => 'terbuka',
        ]);

        $this->actingAs($coordinator)
            ->from(route('admin.needs.edit', $need))
            ->put(route('admin.needs.update', $need), [
                'qty_needed' => 8,
                'priority' => 'normal',
            ])
            ->assertRedirect(route('admin.needs.edit', $need))
            ->assertSessionHasErrors('qty_needed');

        $this->assertDatabaseHas('aid_needs', ['id' => $need->id, 'qty_needed' => 20]);

        $this->actingAs($coordinator)->put(route('admin.needs.update', $need), [
            'qty_needed' => 12,
            'priority' => 'tinggi',
            'deadline' => now()->addDays(4)->toDateString(),
        ])->assertRedirect(route('admin.needs.index'));

        $this->assertDatabaseHas('aid_needs', [
            'id' => $need->id,
            'qty_needed' => 12,
            'priority' => 'tinggi',
        ]);
    }

    public function test_uncommitted_need_is_soft_deleted_with_a_reason(): void
    {
        $coordinator = $this->coordinator();
        $need = $this->need($coordinator, 'Sabun', ['qty_needed' => 10]);

        $this->actingAs($coordinator)->delete(route('admin.needs.destroy', $need), [
            'close_reason' => 'Kebutuhan sudah tidak diperlukan',
        ])->assertRedirect(route('admin.needs.index'));

        $this->assertSoftDeleted('aid_needs', ['id' => $need->id]);
        $this->assertDatabaseHas('aid_needs', [
            'id' => $need->id,
            'status' => 'ditutup',
            'close_reason' => 'Kebutuhan sudah tidak diperlukan',
        ]);
    }

    public function test_committed_need_is_closed_and_kept(): void
    {
        $coordinator = $this->coordinator();
        $need = $this->need($coordinator, 'Makanan Siap Saji', [
            'qty_needed' => 20,
            'qty_pledged' => 5,
        ]);

        $this->actingAs($coordinator)->delete(route('admin.needs.destroy', $need), [
            'close_reason' => 'Posko menerima bantuan dari jalur lain',
        ])->assertRedirect(route('admin.needs.index'));

        $this->assertDatabaseHas('aid_needs', [
            'id' => $need->id,
            'status' => 'ditutup',
            'close_reason' => 'Posko menerima bantuan dari jalur lain',
            'deleted_at' => null,
        ]);
    }

    public function test_public_board_shows_open_needs_and_filters_by_priority(): void
    {
        $coordinator = $this->coordinator();
        $shelterId = $this->shelter($coordinator, 'Posko Baleendah', 'Jl. Adipati Agung, Baleendah');
        $urgentItem = $this->item('Air Darurat');
        $normalItem = $this->item('Selimut Biasa');

        AidNeed::create([
            'shelter_id' => $shelterId,
            'item_id' => $urgentItem->id,
            'qty_needed' => 50,
            'priority' => 'mendesak',
            'status' => 'terbuka',
        ]);
        AidNeed::create([
            'shelter_id' => $shelterId,
            'item_id' => $normalItem->id,
            'qty_needed' => 20,
            'priority' => 'normal',
            'status' => 'terbuka',
        ]);

        $this->get(route('needs.public', ['priority' => 'mendesak', 'region' => 'Baleendah']))
            ->assertOk()
            ->assertSee('Air Darurat')
            ->assertDontSee('Selimut Biasa')
            ->assertSee('Papan Kebutuhan Bantuan');
    }

    public function test_non_coordinator_cannot_access_management_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.needs.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.needs.create'))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.needs.store'), [])->assertForbidden();
    }

    private function coordinator(string $name = 'Koordinator'): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => 'koordinator',
        ]);
    }

    private function item(string $name): Item
    {
        return Item::create([
            'name' => $name,
            'category' => 'pangan',
            'unit' => 'pcs',
            'min_threshold' => 10,
            'is_active' => true,
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function need(User $coordinator, string $itemName, array $overrides = []): AidNeed
    {
        return AidNeed::create(array_merge([
            'shelter_id' => $this->shelter($coordinator),
            'item_id' => $this->item($itemName)->id,
            'qty_needed' => 20,
            'qty_pledged' => 0,
            'qty_fulfilled' => 0,
            'priority' => 'normal',
            'status' => 'terbuka',
        ], $overrides));
    }

    private function shelter(User $coordinator, string $name = 'Posko Utama', string $address = 'Jl. Merdeka'): int
    {
        $eventId = DB::table('disaster_events')->insertGetId([
            'name' => 'Banjir Testing '.fake()->unique()->numberBetween(1, 99999),
            'disaster_type' => 'banjir',
            'severity' => 'sedang',
            'status' => 'aktif',
            'regency' => 'Kabupaten Bandung',
            'lat' => 0,
            'lng' => 0,
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('shelters')->insertGetId([
            'disaster_event_id' => $eventId,
            'coordinator_id' => $coordinator->id,
            'name' => $name,
            'address' => $address,
            'lat' => 0,
            'lng' => 0,
            'capacity' => 100,
            'occupancy' => 0,
            'contact' => '000',
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
