<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ItemCatalogReadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User { return User::factory()->create(['role' => 'admin']); }

    public function test_admin_can_see_paginated_catalog(): void
    {
        $item = Item::create(['name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg', 'min_threshold' => 10]);
        $this->actingAs($this->admin())->get(route('admin.items.index'))->assertOk()->assertSee('Beras')->assertSee('Kebutuhan posko:')->assertSee('Stok:');
    }

    public function test_catalog_can_search_by_name(): void
    {
        Item::create(['name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg']);
        Item::create(['name' => 'Selimut', 'category' => 'sandang', 'unit' => 'pcs']);
        $this->actingAs($this->admin())->get(route('admin.items.index', ['search' => 'Beras']))->assertSee('Beras')->assertDontSee('Selimut');
    }

    public function test_catalog_can_filter_by_category(): void
    {
        Item::create(['name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg']);
        Item::create(['name' => 'Sabun', 'category' => 'kebersihan', 'unit' => 'pcs']);
        $this->actingAs($this->admin())->get(route('admin.items.index', ['category' => 'kebersihan']))->assertSee('Sabun')->assertDontSee('Beras');
    }

    public function test_catalog_shows_correct_usage_counts_and_zero_stock(): void
    {
        $item = Item::create(['name' => 'Beras', 'category' => 'pangan', 'unit' => 'kg']);
        $admin = $this->admin();
        $event = DB::table('disaster_events')->insertGetId(['name' => 'Banjir', 'disaster_type' => 'banjir', 'severity' => 'sedang', 'status' => 'aktif', 'regency' => 'Kota', 'lat' => 0, 'lng' => 0, 'started_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $shelterOne = DB::table('shelters')->insertGetId(['disaster_event_id' => $event, 'coordinator_id' => $admin->id, 'name' => 'Posko 1', 'address' => 'Jalan 1', 'lat' => 0, 'lng' => 0, 'capacity' => 100, 'contact' => '000']);
        $shelterTwo = DB::table('shelters')->insertGetId(['disaster_event_id' => $event, 'coordinator_id' => $admin->id, 'name' => 'Posko 2', 'address' => 'Jalan 2', 'lat' => 0, 'lng' => 0, 'capacity' => 100, 'contact' => '000']);
        $packageOne = DB::table('aid_packages')->insertGetId(['name' => 'Paket 1', 'target' => 'keluarga', 'created_by' => $admin->id, 'created_at' => now(), 'updated_at' => now()]);
        $packageTwo = DB::table('aid_packages')->insertGetId(['name' => 'Paket 2', 'target' => 'keluarga', 'created_by' => $admin->id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('aid_needs')->insert([
            ['item_id' => $item->id, 'shelter_id' => $shelterOne, 'qty_needed' => 10, 'priority' => 'normal', 'status' => 'terbuka', 'created_at' => now(), 'updated_at' => now()],
            ['item_id' => $item->id, 'shelter_id' => $shelterTwo, 'qty_needed' => 5, 'priority' => 'normal', 'status' => 'terbuka', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('aid_package_items')->insert([
            ['aid_package_id' => $packageOne, 'item_id' => $item->id, 'quantity' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['aid_package_id' => $packageTwo, 'item_id' => $item->id, 'quantity' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($this->admin())->get(route('admin.items.index'))->assertSee('Kebutuhan posko: <b>2</b>', false)->assertSee('Paket: <b>2</b>', false)->assertSee('Stok: <b>0</b>', false);
    }
}
