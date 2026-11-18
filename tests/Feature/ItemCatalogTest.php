<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ItemCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_item_to_catalog(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->post(route('admin.items.store'), [
            'name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg', 'min_threshold' => 25,
        ]);

        $response->assertRedirect(route('admin.items.create'))->assertSessionHas('success');
        $this->assertDatabaseHas('items', ['name' => 'Beras', 'unit' => 'kg', 'min_threshold' => 25]);
    }

    public function test_duplicate_name_and_unit_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Item::create(['name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg', 'min_threshold' => 10]);

        $response = $this->actingAs($admin)->from(route('admin.items.create'))->post(route('admin.items.store'), [
            'name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg', 'min_threshold' => 20,
        ]);

        $response->assertRedirect(route('admin.items.create'))->assertSessionHasErrors('name');
        $this->assertDatabaseCount('items', 1);
    }

    public function test_non_admin_is_forbidden_from_item_catalog(): void
    {
        $user = User::factory()->create(['role' => 'relawan']);
        $this->actingAs($user)->get(route('admin.items.create'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.items.store'), [])->assertForbidden();
    }

    public function test_admin_can_update_item(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::create(['name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg', 'min_threshold' => 10]);

        $this->actingAs($admin)->put(route('admin.items.update', $item), [
            'name' => 'Beras Premium', 'category' => 'pangan', 'unit' => 'kg', 'min_threshold' => 25,
        ])->assertRedirect(route('admin.items.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('items', ['id' => $item->id, 'name' => 'Beras Premium', 'min_threshold' => 25]);
    }

    public function test_unit_cannot_be_changed_when_item_has_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::create(['name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg', 'min_threshold' => 10]);
        $eventId = DB::table('disaster_events')->insertGetId([
            'name' => 'Banjir', 'disaster_type' => 'banjir', 'severity' => 'sedang', 'status' => 'aktif',
            'regency' => 'Bandung', 'lat' => 0, 'lng' => 0, 'started_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $shelterId = DB::table('shelters')->insertGetId([
            'disaster_event_id' => $eventId, 'coordinator_id' => $admin->id, 'name' => 'Posko', 'address' => 'Alamat',
            'lat' => 0, 'lng' => 0, 'capacity' => 100, 'contact' => '000', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('stocks')->insert(['shelter_id' => $shelterId, 'item_id' => $item->id, 'quantity' => 5, 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($admin)->from(route('admin.items.edit', $item))->put(route('admin.items.update', $item), [
            'name' => 'Beras', 'category' => 'pangan', 'unit' => 'dus', 'min_threshold' => 20,
        ])->assertRedirect(route('admin.items.edit', $item))->assertSessionHasErrors('unit');

        $this->assertDatabaseHas('items', ['id' => $item->id, 'unit' => 'kg', 'min_threshold' => 10]);
    }

    public function test_duplicate_name_and_unit_is_rejected_when_updating(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Item::create(['name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg', 'min_threshold' => 10]);
        $item = Item::create(['name' => 'Gula', 'category' => 'pangan', 'unit' => 'kg', 'min_threshold' => 10]);

        $this->actingAs($admin)->from(route('admin.items.edit', $item))->put(route('admin.items.update', $item), [
            'name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg', 'min_threshold' => 20,
        ])->assertRedirect(route('admin.items.edit', $item))->assertSessionHasErrors('name');
    }

    public function test_non_admin_cannot_update_item(): void
    {
        $user = User::factory()->create(['role' => 'relawan']);
        $item = Item::create(['name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg', 'min_threshold' => 10]);

        $this->actingAs($user)->get(route('admin.items.edit', $item))->assertForbidden();
        $this->actingAs($user)->put(route('admin.items.update', $item), [])->assertForbidden();
    }

    public function test_admin_can_delete_item_without_usage(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::create(['name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg', 'min_threshold' => 10]);

        $this->actingAs($admin)->delete(route('admin.items.destroy', $item))
            ->assertRedirect(route('admin.items.index'))->assertSessionHas('success');

        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }

    public function test_used_item_is_deactivated_instead_of_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::create(['name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg', 'min_threshold' => 10]);
        $eventId = DB::table('disaster_events')->insertGetId([
            'name' => 'Banjir', 'disaster_type' => 'banjir', 'severity' => 'sedang', 'status' => 'aktif',
            'regency' => 'Bandung', 'lat' => 0, 'lng' => 0, 'started_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $shelterId = DB::table('shelters')->insertGetId([
            'disaster_event_id' => $eventId, 'coordinator_id' => $admin->id, 'name' => 'Posko', 'address' => 'Alamat',
            'lat' => 0, 'lng' => 0, 'capacity' => 100, 'contact' => '000', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('aid_needs')->insert([
            'shelter_id' => $shelterId, 'item_id' => $item->id, 'qty_needed' => 10,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($admin)->delete(route('admin.items.destroy', $item));

        $this->assertDatabaseHas('items', ['id' => $item->id, 'is_active' => false]);
    }

    public function test_inactive_item_is_not_available_for_new_choices(): void
    {
        $active = Item::create(['name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg']);
        $inactive = Item::create(['name' => 'Gula', 'category' => 'pangan', 'unit' => 'kg', 'is_active' => false]);

        $this->assertTrue(Item::active()->whereKey($active)->exists());
        $this->assertFalse(Item::active()->whereKey($inactive)->exists());
    }

    public function test_non_admin_cannot_delete_item(): void
    {
        $user = User::factory()->create(['role' => 'relawan']);
        $item = Item::create(['name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg']);

        $this->actingAs($user)->delete(route('admin.items.destroy', $item))->assertForbidden();
        $this->assertDatabaseHas('items', ['id' => $item->id]);
    }

    public function test_admin_can_reactivate_inactive_item(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::create(['name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg', 'is_active' => false]);

        $this->actingAs($admin)->patch(route('admin.items.activate', $item))
            ->assertRedirect(route('admin.items.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('items', ['id' => $item->id, 'is_active' => true]);
    }
}
