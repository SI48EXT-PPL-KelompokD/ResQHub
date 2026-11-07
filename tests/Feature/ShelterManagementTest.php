<?php

namespace Tests\Feature;

use App\Models\DisasterEvent;
use App\Models\Shelter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Admin\ShelterManagement;

class ShelterManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_koordinator_can_access_shelter_management_page(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();

        $response = $this->actingAs($koordinator)->get('/admin/shelters');

        $response->assertStatus(200);
        $response->assertSee('Posko Evakuasi');
        $response->assertSee('PBI-04');
    }

    public function test_admin_can_access_shelter_management_page(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/shelters');

        $response->assertStatus(200);
        $response->assertSee('Posko Evakuasi');
    }

    public function test_warga_cannot_access_shelter_management_page(): void
    {
        $warga = User::where('role', 'warga')->first();

        $response = $this->actingAs($warga)->get('/admin/shelters');

        $response->assertStatus(403);
    }

    public function test_koordinator_can_create_new_shelter_with_valid_data(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $activeEvent = DisasterEvent::where('status', 'aktif')->first();

        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->set('disaster_event_id', $activeEvent->id)
            ->set('name', 'Posko Pengungsian Balai Warga RW 05')
            ->set('address', 'Jl. Sukamenak Indah No. 12')
            ->set('lat', -6.9500000)
            ->set('lng', 107.5900000)
            ->set('capacity', 120)
            ->set('contact', '081299887766 (Bpk. Mulyadi)')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('shelters', [
            'name'           => 'Posko Pengungsian Balai Warga RW 05',
            'coordinator_id' => $koordinator->id,
            'capacity'       => 120,
            'occupancy'      => 0,
            'status'         => 'aktif',
        ]);
    }

    public function test_shelter_creation_fails_if_capacity_is_zero_or_negative(): void
    {
        $koordinator = User::where('role', 'koordinator')->first();
        $activeEvent = DisasterEvent::where('status', 'aktif')->first();

        Livewire::actingAs($koordinator)
            ->test(ShelterManagement::class)
            ->set('disaster_event_id', $activeEvent->id)
            ->set('name', 'Posko Rusak')
            ->set('address', 'Jl. Test')
            ->set('lat', -6.95)
            ->set('lng', 107.59)
            ->set('capacity', 0)
            ->set('contact', '08123')
            ->call('store')
            ->assertHasErrors(['capacity']);
    }
}
