<?php

namespace Tests\Feature;

use App\Models\AidPackage;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AidPackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_coordinator_can_create_a_package_with_multiple_items(): void
    {
        $coordinator = $this->coordinator();
        $rice = Item::factory()->create(['name' => 'Beras Paket']);
        $water = Item::factory()->create(['name' => 'Air Paket']);

        $response = $this->actingAs($coordinator)->post(route('admin.packages.store'), [
            'name' => 'Paket Keluarga Uji',
            'target' => 'keluarga',
            'description' => 'Untuk tiga hari.',
            'items' => [
                ['item_id' => $rice->id, 'quantity' => 5],
                ['item_id' => $water->id, 'quantity' => 2],
            ],
        ]);

        $package = AidPackage::query()->where('name', 'Paket Keluarga Uji')->firstOrFail();

        $response->assertRedirect(route('admin.packages.index'))->assertSessionHas('success');
        $this->assertDatabaseHas('aid_package_items', ['aid_package_id' => $package->id, 'item_id' => $rice->id, 'quantity' => 5]);
        $this->assertDatabaseHas('aid_package_items', ['aid_package_id' => $package->id, 'item_id' => $water->id, 'quantity' => 2]);
    }

    public function test_package_requires_at_least_one_item(): void
    {
        $coordinator = $this->coordinator();

        $response = $this->actingAs($coordinator)
            ->from(route('admin.packages.create'))
            ->post(route('admin.packages.store'), [
                'name' => 'Paket Tanpa Isi',
                'target' => 'jiwa',
                'items' => [],
            ]);

        $response->assertRedirect(route('admin.packages.create'))->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('aid_packages', ['name' => 'Paket Tanpa Isi']);
    }

    public function test_package_calculation_multiplies_each_item_quantity(): void
    {
        $coordinator = $this->coordinator();
        $item = Item::factory()->create(['name' => 'Beras Hitung', 'unit' => 'kg']);
        $package = AidPackage::create([
            'name' => 'Paket Hitung',
            'target' => 'jiwa',
            'created_by' => $coordinator->id,
        ]);
        $package->packageItems()->create(['item_id' => $item->id, 'quantity' => 3]);

        $response = $this->actingAs($coordinator)->get(route('admin.packages.show', [$package, 'target_count' => 4]));

        $response->assertOk()->assertSee('12 kg')->assertSee('Beras Hitung');
        $this->assertSame(12, $package->fresh()->load('packageItems')->requirementsFor(4)[0]['total']);
    }

    public function test_used_package_is_deactivated_instead_of_deleted(): void
    {
        $coordinator = $this->coordinator();
        $package = $this->package($coordinator);
        $shelterId = $this->shelter($coordinator);

        DB::table('distributions')->insert([
            'shelter_id' => $shelterId,
            'aid_package_id' => $package->id,
            'quantity' => 1,
            'distributed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($coordinator)->delete(route('admin.packages.destroy', $package));

        $response->assertRedirect(route('admin.packages.index'))->assertSessionHas('success');
        $this->assertDatabaseHas('aid_packages', ['id' => $package->id, 'is_active' => false]);
    }

    public function test_used_package_cannot_change_its_item_composition(): void
    {
        $coordinator = $this->coordinator();
        $package = $this->package($coordinator);
        $shelterId = $this->shelter($coordinator);
        $newItem = Item::factory()->create(['name' => 'Barang Baru']);

        DB::table('distributions')->insert([
            'shelter_id' => $shelterId,
            'aid_package_id' => $package->id,
            'quantity' => 1,
            'distributed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($coordinator)
            ->from(route('admin.packages.edit', $package))
            ->put(route('admin.packages.update', $package), [
                'name' => 'Paket Tetap',
                'target' => 'keluarga',
                'items' => [['item_id' => $newItem->id, 'quantity' => 9]],
            ]);

        $response->assertRedirect(route('admin.packages.edit', $package))->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('aid_package_items', ['aid_package_id' => $package->id, 'item_id' => $newItem->id]);
    }

    private function coordinator(): User
    {
        return User::factory()->create(['role' => 'koordinator']);
    }

    private function package(User $coordinator): AidPackage
    {
        $item = Item::factory()->create(['name' => 'Barang Paket']);
        $package = AidPackage::create([
            'name' => 'Paket Riwayat',
            'target' => 'keluarga',
            'created_by' => $coordinator->id,
        ]);
        $package->packageItems()->create(['item_id' => $item->id, 'quantity' => 2]);

        return $package;
    }

    private function shelter(User $coordinator): int
    {
        $eventId = DB::table('disaster_events')->insertGetId([
            'name' => 'Bencana Uji',
            'disaster_type' => 'banjir',
            'severity' => 'sedang',
            'status' => 'aktif',
            'regency' => 'Kabupaten Bandung',
            'lat' => -6.9,
            'lng' => 107.6,
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('shelters')->insertGetId([
            'disaster_event_id' => $eventId,
            'coordinator_id' => $coordinator->id,
            'name' => 'Posko Uji',
            'address' => 'Jalan Uji',
            'lat' => -6.9,
            'lng' => 107.6,
            'capacity' => 100,
            'occupancy' => 0,
            'contact' => '0800000000',
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
