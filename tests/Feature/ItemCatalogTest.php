<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
