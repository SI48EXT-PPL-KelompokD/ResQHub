<?php

namespace Tests\Feature;

use App\Livewire\Admin\AidNeedForm;
use App\Livewire\Admin\StockManagement;
use App\Models\AidNeed;
use App\Models\DisasterEvent;
use App\Models\Item;
use App\Models\Shelter;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class StockLogisticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $coordinator;
    protected User $otherCoordinator;
    protected User $warga;
    protected Shelter $shelter;
    protected Shelter $otherShelter;
    protected Item $riceItem;
    protected Item $waterItem;
    protected Item $blanketItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin BPBD',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->coordinator = User::create([
            'name' => 'Koordinator Budi',
            'email' => 'budi@test.com',
            'password' => Hash::make('password'),
            'role' => 'koordinator',
            'is_active' => true,
        ]);

        $this->otherCoordinator = User::create([
            'name' => 'Koordinator Siti',
            'email' => 'siti@test.com',
            'password' => Hash::make('password'),
            'role' => 'koordinator',
            'is_active' => true,
        ]);

        $this->warga = User::create([
            'name' => 'Warga Terdampak',
            'email' => 'warga@test.com',
            'password' => Hash::make('password'),
            'role' => 'warga',
            'is_active' => true,
        ]);

        $disaster = DisasterEvent::create([
            'name' => 'Banjir Baleendah',
            'disaster_type' => 'banjir',
            'severity' => 'sedang',
            'status' => 'aktif',
            'regency' => 'Bandung',
            'lat' => -6.99,
            'lng' => 107.62,
            'started_at' => now(),
        ]);

        $this->shelter = Shelter::create([
            'disaster_event_id' => $disaster->id,
            'coordinator_id' => $this->coordinator->id,
            'name' => 'Posko Baleendah',
            'address' => 'Jl. Raya Baleendah No. 1',
            'lat' => -6.992,
            'lng' => 107.625,
            'capacity' => 200,
            'occupancy' => 80,
            'contact' => '081234567890',
            'status' => 'aktif',
        ]);

        $this->otherShelter = Shelter::create([
            'disaster_event_id' => $disaster->id,
            'coordinator_id' => $this->otherCoordinator->id,
            'name' => 'Posko Dayeuhkolot',
            'address' => 'Jl. Dayeuhkolot No. 2',
            'lat' => -6.985,
            'lng' => 107.620,
            'capacity' => 150,
            'occupancy' => 60,
            'contact' => '081234567891',
            'status' => 'aktif',
        ]);

        $this->riceItem = Item::create([
            'name' => 'Beras Medium',
            'category' => 'pangan',
            'unit' => 'kg',
            'min_threshold' => 100,
            'is_active' => true,
        ]);

        $this->waterItem = Item::create([
            'name' => 'Air Mineral Dus',
            'category' => 'pangan',
            'unit' => 'dus',
            'min_threshold' => 30,
            'is_active' => true,
        ]);

        $this->blanketItem = Item::create([
            'name' => 'Selimut Hangat',
            'category' => 'sandang',
            'unit' => 'lembar',
            'min_threshold' => 50,
            'is_active' => true,
        ]);
    }

    /**
     * 1. Hak Akses (Authorization)
     */
    public function test_guest_cannot_access_stocks(): void
    {
        $response = $this->get(route('admin.stocks.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthorized_roles_get_forbidden(): void
    {
        $response = $this->actingAs($this->warga)->get(route('admin.stocks.index'));
        $response->assertStatus(403);
    }

    public function test_coordinator_and_admin_can_access_stock_page(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('admin.stocks.index'));
        $response->assertStatus(200);

        $responseAdmin = $this->actingAs($this->admin)->get(route('admin.stocks.index'));
        $responseAdmin->assertStatus(200);
    }

    /**
     * 2. TAMBAH (Create) - Stok Awal / Barang Masuk di luar donasi
     */
    public function test_coordinator_can_record_incoming_stock_as_movement_in(): void
    {
        Livewire::actingAs($this->coordinator)
            ->test(StockManagement::class)
            ->set('incomingShelterId', $this->shelter->id)
            ->set('incomingItemId', $this->riceItem->id)
            ->set('incomingQuantity', 250)
            ->set('incomingSource', 'Bantuan Pemerintah')
            ->set('incomingMinThreshold', 80)
            ->set('incomingNotes', 'Drop logistik dari BPBD Jawa Barat')
            ->call('saveIncomingStock')
            ->assertHasNoErrors()
            ->assertSet('showIncomingModal', false);

        // Verifikasi stok tersimpan
        $this->assertDatabaseHas('stocks', [
            'shelter_id' => $this->shelter->id,
            'item_id' => $this->riceItem->id,
            'quantity' => 250,
            'min_threshold' => 80,
        ]);

        // Verifikasi tercatat sebagai mutasi masuk
        $stock = Stock::where('shelter_id', $this->shelter->id)
            ->where('item_id', $this->riceItem->id)
            ->first();

        $this->assertNotNull($stock);
        $this->assertDatabaseHas('stock_movements', [
            'stock_id' => $stock->id,
            'type' => 'masuk',
            'quantity' => 250,
            'created_by' => $this->coordinator->id,
        ]);

        $movement = StockMovement::where('stock_id', $stock->id)->first();
        $this->assertStringContainsString('Bantuan Pemerintah', $movement->reason);
        $this->assertStringContainsString('Drop logistik dari BPBD', $movement->reason);
    }

    public function test_incoming_stock_accumulates_existing_quantity(): void
    {
        $stock = Stock::create([
            'shelter_id' => $this->shelter->id,
            'item_id' => $this->riceItem->id,
            'quantity' => 100,
            'min_threshold' => 50,
        ]);

        Livewire::actingAs($this->coordinator)
            ->test(StockManagement::class)
            ->set('incomingShelterId', $this->shelter->id)
            ->set('incomingItemId', $this->riceItem->id)
            ->set('incomingQuantity', 50)
            ->set('incomingSource', 'Stok Opname')
            ->set('incomingMinThreshold', 60)
            ->call('saveIncomingStock')
            ->assertHasNoErrors();

        $stock->refresh();
        $this->assertEquals(150, $stock->quantity);
        $this->assertEquals(60, $stock->min_threshold);

        $this->assertDatabaseHas('stock_movements', [
            'stock_id' => $stock->id,
            'type' => 'masuk',
            'quantity' => 50,
        ]);
    }

    /**
     * 3. LIHAT (Read) - Status Menipis dan Estimasi Hari Tersisa
     */
    public function test_displays_depleted_status_when_stock_below_threshold(): void
    {
        // Beras: 20 kg, ambang: 50 kg -> Menipis (< 50)
        Stock::create([
            'shelter_id' => $this->shelter->id,
            'item_id' => $this->riceItem->id,
            'quantity' => 20,
            'min_threshold' => 50,
        ]);

        Livewire::actingAs($this->coordinator)
            ->test(StockManagement::class)
            ->assertSee('Beras Medium')
            ->assertSee('Menipis');
    }

    public function test_estimates_days_remaining_from_last_3_days_distribution(): void
    {
        $stock = Stock::create([
            'shelter_id' => $this->shelter->id,
            'item_id' => $this->waterItem->id,
            'quantity' => 30, // 30 dus tersisa
            'min_threshold' => 20,
        ]);

        // Catat penyaluran keluar dalam 3 hari terakhir: total 15 dus -> rata-rata 5 dus/hari
        // Estimasi: 30 / 5 = 6.0 hari
        StockMovement::create([
            'stock_id' => $stock->id,
            'type' => 'keluar',
            'quantity' => 15,
            'reason' => 'Penyaluran dapur umum',
            'created_by' => $this->coordinator->id,
            'created_at' => Carbon::now()->subDays(1),
        ]);

        $estimate = $stock->days_remaining_estimate;
        $this->assertEquals(6.0, $estimate['days']);
        $this->assertEquals(5.0, $estimate['avg_daily']);
        $this->assertEquals('6 hari', $estimate['label']);

        Livewire::actingAs($this->coordinator)
            ->test(StockManagement::class)
            ->assertSee('Air Mineral Dus')
            ->assertSee('6 hari');
    }

    /**
     * 4. UBAH (Update) - Penyesuaian Stok (Alasan Wajib, Selisih Tercatat Mutasi)
     */
    public function test_adjustment_requires_reason(): void
    {
        $stock = Stock::create([
            'shelter_id' => $this->shelter->id,
            'item_id' => $this->riceItem->id,
            'quantity' => 100,
            'min_threshold' => 30,
        ]);

        Livewire::actingAs($this->coordinator)
            ->test(StockManagement::class)
            ->call('openAdjustModal', $stock->id)
            ->set('adjustNewQuantity', 85)
            ->set('adjustReason', '') // Kosong -> harus gagal
            ->call('saveAdjustment')
            ->assertHasErrors(['adjustReason']);

        $stock->refresh();
        $this->assertEquals(100, $stock->quantity);
    }

    public function test_adjustment_updates_quantity_and_logs_difference_as_movement(): void
    {
        $stock = Stock::create([
            'shelter_id' => $this->shelter->id,
            'item_id' => $this->riceItem->id,
            'quantity' => 100,
            'min_threshold' => 30,
        ]);

        Livewire::actingAs($this->coordinator)
            ->test(StockManagement::class)
            ->call('openAdjustModal', $stock->id)
            ->set('adjustNewQuantity', 85) // Berkurang 15 kg
            ->set('adjustReason', '15 kg beras rusak terkena rembesan air hujan di gudang')
            ->set('adjustMinThreshold', 35)
            ->call('saveAdjustment')
            ->assertHasNoErrors()
            ->assertSet('showAdjustModal', false);

        $stock->refresh();
        $this->assertEquals(85, $stock->quantity);
        $this->assertEquals(35, $stock->min_threshold);

        // Verifikasi selisih (-15) tercatat sebagai mutasi penyesuaian
        $this->assertDatabaseHas('stock_movements', [
            'stock_id' => $stock->id,
            'type' => 'penyesuaian',
            'quantity' => -15,
            'created_by' => $this->coordinator->id,
        ]);
    }

    /**
     * 5. HAPUS (Delete) - Hanya bila jumlah 0 dan tidak memiliki kebutuhan terbuka
     */
    public function test_cannot_delete_stock_if_quantity_greater_than_zero(): void
    {
        $stock = Stock::create([
            'shelter_id' => $this->shelter->id,
            'item_id' => $this->blanketItem->id,
            'quantity' => 10,
            'min_threshold' => 20,
        ]);

        $test = Livewire::actingAs($this->coordinator)
            ->test(StockManagement::class)
            ->call('openDeleteModal', $stock->id)
            ->assertSet('showDeleteModal', true)
            ->call('deleteStockAction');

        $this->assertNotNull($test->get('deleteBlockReason'));
        $this->assertDatabaseHas('stocks', ['id' => $stock->id, 'deleted_at' => null]);
    }

    public function test_cannot_delete_stock_if_has_open_need(): void
    {
        $stock = Stock::create([
            'shelter_id' => $this->shelter->id,
            'item_id' => $this->blanketItem->id,
            'quantity' => 0, // Kuantitas 0
            'min_threshold' => 20,
        ]);

        // Buat kebutuhan terbuka untuk barang ini di posko ini
        AidNeed::create([
            'shelter_id' => $this->shelter->id,
            'item_id' => $this->blanketItem->id,
            'qty_needed' => 50,
            'priority' => 'tinggi',
            'status' => 'terbuka',
        ]);

        $test = Livewire::actingAs($this->coordinator)
            ->test(StockManagement::class)
            ->call('openDeleteModal', $stock->id)
            ->call('deleteStockAction');

        $this->assertNotNull($test->get('deleteBlockReason'));
        $this->assertDatabaseHas('stocks', ['id' => $stock->id, 'deleted_at' => null]);
    }

    public function test_can_delete_stock_when_zero_and_no_open_needs_preserving_history(): void
    {
        $stock = Stock::create([
            'shelter_id' => $this->shelter->id,
            'item_id' => $this->blanketItem->id,
            'quantity' => 0,
            'min_threshold' => 20,
        ]);

        // Simpan mutasi historis
        $movement = StockMovement::create([
            'stock_id' => $stock->id,
            'type' => 'masuk',
            'quantity' => 50,
            'reason' => 'Stok awal',
            'created_by' => $this->coordinator->id,
        ]);

        $test = Livewire::actingAs($this->coordinator)
            ->test(StockManagement::class)
            ->call('openDeleteModal', $stock->id);

        $this->assertNull($test->get('deleteBlockReason'));

        $test->call('deleteStockAction')
            ->assertHasNoErrors()
            ->assertSet('showDeleteModal', false);

        // Verifikasi stock terhapus (soft deleted)
        $this->assertSoftDeleted('stocks', ['id' => $stock->id]);

        // Verifikasi riwayat mutasi tetap tersimpan aman di database
        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'stock_id' => $stock->id,
            'quantity' => 50,
        ]);
    }

    public function test_can_open_pbi11_modal_and_submit_need(): void
    {
        $stock = Stock::create([
            'shelter_id' => $this->shelter->id,
            'item_id' => $this->riceItem->id,
            'quantity' => 15,
            'min_threshold' => 100,
        ]);

        Livewire::actingAs($this->coordinator)
            ->test(StockManagement::class)
            ->call('openNeedModal', $stock->id)
            ->assertSet('showNeedModal', true)
            ->assertSet('needItemId', $stock->item_id)
            ->assertSet('needShelterId', $stock->shelter_id)
            ->assertSee('Beras Medium')
            ->set('needQty', 85)
            ->set('needPriority', 'tinggi')
            ->call('submitAidNeed')
            ->assertHasNoErrors()
            ->assertSet('showNeedModal', false);

        $this->assertDatabaseHas('aid_needs', [
            'shelter_id' => $stock->shelter_id,
            'item_id' => $stock->item_id,
            'qty_needed' => 85,
            'priority' => 'tinggi',
            'status' => 'terbuka',
        ]);
    }

    /**
     * 6. Form PBI-11 (Ajukan Kebutuhan) dengan barang terisi
     */
    public function test_can_open_pbi11_form_with_prefilled_item_and_shelter(): void
    {
        $stock = Stock::create([
            'shelter_id' => $this->shelter->id,
            'item_id' => $this->riceItem->id,
            'quantity' => 15,
            'min_threshold' => 100,
        ]);

        // Akses route form PBI-11 langsung dengan query param
        $response = $this->actingAs($this->coordinator)
            ->get(route('admin.aid-needs.create', [
                'shelter_id' => $stock->shelter_id,
                'item_id' => $stock->item_id,
            ]));

        $response->assertStatus(200);
        $response->assertSee('Beras Medium');
        $response->assertSee('Posko Baleendah');

        // Submit via Livewire AidNeedForm
        Livewire::actingAs($this->coordinator)
            ->withQueryParams([
                'shelter_id' => $stock->shelter_id,
                'item_id' => $stock->item_id,
            ])
            ->test(AidNeedForm::class)
            ->assertSet('shelter_id', $stock->shelter_id)
            ->assertSet('item_id', $stock->item_id)
            ->set('qty_needed', 85)
            ->set('priority', 'tinggi')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.stocks.index'));

        $this->assertDatabaseHas('aid_needs', [
            'shelter_id' => $stock->shelter_id,
            'item_id' => $stock->item_id,
            'qty_needed' => 85,
            'priority' => 'tinggi',
            'status' => 'terbuka',
        ]);
    }
}
