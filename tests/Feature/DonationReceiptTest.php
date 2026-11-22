<?php

namespace Tests\Feature;

use App\Livewire\Admin\DonationReceiptManagement;
use App\Models\AidNeed;
use App\Models\DisasterEvent;
use App\Models\DonationReceipt;
use App\Models\GoodsDonation;
use App\Models\Item;
use App\Models\Shelter;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Notifications\DonationReceivedNotification;
use App\Services\DonationReceiptService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class DonationReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $coordinator;
    protected User $otherCoordinator;
    protected User $donor;
    protected User $warga;
    protected Shelter $shelter;
    protected Shelter $otherShelter;
    protected Item $item;
    protected AidNeed $aidNeed;
    protected GoodsDonation $goodsDonation;

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

        $this->donor = User::create([
            'name' => 'Donatur Dermawan',
            'email' => 'donor@test.com',
            'password' => Hash::make('password'),
            'role' => 'donatur',
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
            'started_at' => Carbon::now()->subDays(5),
            'description' => 'Banjir luapan Sungai Citarum',
        ]);

        $this->shelter = Shelter::create([
            'disaster_event_id' => $disaster->id,
            'name' => 'Posko Utama GOR Baleendah',
            'address' => 'Jl. Adipati Agung No. 1',
            'lat' => -6.992,
            'lng' => 107.625,
            'capacity' => 500,
            'occupancy' => 200,
            'status' => 'aktif',
            'coordinator_id' => $this->coordinator->id,
            'contact' => '081234567890',
        ]);

        $this->otherShelter = Shelter::create([
            'disaster_event_id' => $disaster->id,
            'name' => 'Posko Kantor Kecamatan',
            'address' => 'Jl. Raya Dayeuhkolot No. 10',
            'lat' => -6.985,
            'lng' => 107.620,
            'capacity' => 300,
            'occupancy' => 150,
            'status' => 'aktif',
            'coordinator_id' => $this->otherCoordinator->id,
            'contact' => '081234567891',
        ]);

        $this->item = Item::create([
            'name' => 'Air Mineral Botol 600ml',
            'category' => 'pangan',
            'unit' => 'dus',
            'description' => 'Air mineral kemasan dus isi 24 botol',
            'is_active' => true,
        ]);

        $this->aidNeed = AidNeed::create([
            'shelter_id' => $this->shelter->id,
            'item_id' => $this->item->id,
            'qty_needed' => 100,
            'qty_pledged' => 50,
            'qty_fulfilled' => 0,
            'priority' => 'mendesak',
            'status' => 'terbuka',
        ]);

        $this->goodsDonation = GoodsDonation::create([
            'code' => 'GDN-2611-TEST1',
            'donor_id' => $this->donor->id,
            'aid_need_id' => $this->aidNeed->id,
            'quantity' => 50,
            'delivery_method' => 'antar',
            'est_arrival' => Carbon::today(),
            'status' => 'dijanjikan',
        ]);
    }

    /**
     * Test 1: Tamu dialihkan ke login
     */
    public function test_guest_cannot_access_donation_receipts(): void
    {
        $response = $this->get('/admin/donation-receipts');
        $response->assertRedirect('/login');
    }

    /**
     * Test 2: Role tidak berwenang dilarang (403)
     */
    public function test_unauthorized_roles_get_forbidden(): void
    {
        $response = $this->actingAs($this->warga)->get('/admin/donation-receipts');
        $response->assertStatus(403);

        $donorResponse = $this->actingAs($this->donor)->get('/admin/donation-receipts');
        $donorResponse->assertStatus(403);
    }

    /**
     * Test 3: Koordinator dan Admin bisa mengakses halaman penerimaan donasi
     */
    public function test_coordinator_and_admin_can_access_page(): void
    {
        $response = $this->actingAs($this->coordinator)->get('/admin/donation-receipts');
        $response->assertStatus(200);

        $adminResponse = $this->actingAs($this->admin)->get('/admin/donation-receipts');
        $adminResponse->assertStatus(200);
    }

    /**
     * Test 4 (AC 1 Tambah): Koordinator mencatat penerimaan donasi
     * - Jumlah baik menambah stok posko
     * - Tercatat mutasi masuk di stock_movements
     * - Menambah jumlah terpenuhi pada kebutuhan
     * - Status donasi menjadi "Diterima"
     * - Donatur menerima notifikasi
     */
    public function test_coordinator_can_record_donation_receipt(): void
    {
        Notification::fake();

        $service = app(DonationReceiptService::class);
        $receipt = $service->recordReceipt(
            $this->goodsDonation,
            $this->shelter->id,
            45, // qty good
            5,  // qty damaged
            $this->coordinator,
            Carbon::now()
        );

        $this->assertDatabaseHas('donation_receipts', [
            'id' => $receipt->id,
            'goods_donation_id' => $this->goodsDonation->id,
            'shelter_id' => $this->shelter->id,
            'qty_good' => 45,
            'qty_damaged' => 5,
            'received_by' => $this->coordinator->id,
        ]);

        // Status donasi menjadi 'diterima'
        $this->assertEquals('diterima', $this->goodsDonation->fresh()->status);

        // Stok bertambah 45
        $stock = Stock::where('shelter_id', $this->shelter->id)->where('item_id', $this->item->id)->first();
        $this->assertNotNull($stock);
        $this->assertEquals(45, $stock->quantity);

        // Mutasi masuk tercatat
        $movement = StockMovement::where('donation_receipt_id', $receipt->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals('masuk', $movement->type);
        $this->assertEquals(45, $movement->quantity);

        // Kebutuhan posko qty_fulfilled bertambah
        $this->assertEquals(45, $this->aidNeed->fresh()->qty_fulfilled);

        // Notifikasi terkirim ke donatur
        Notification::assertSentTo($this->donor, DonationReceivedNotification::class);
    }

    /**
     * Test 5 (AC 1): Pemenuhan kebutuhan otomatis status 'terpenuhi' saat target tercapai
     */
    public function test_recording_receipt_fulfills_need_when_target_reached(): void
    {
        $this->aidNeed->update(['qty_needed' => 40]);

        $service = app(DonationReceiptService::class);
        $service->recordReceipt(
            $this->goodsDonation,
            $this->shelter->id,
            45,
            5,
            $this->coordinator
        );

        $this->assertEquals('terpenuhi', $this->aidNeed->fresh()->status);
    }

    /**
     * Test 6 (AC 1): Livewire component dapat submit form penerimaan donasi
     */
    public function test_livewire_can_submit_donation_receipt(): void
    {
        Livewire::actingAs($this->coordinator)
            ->test(DonationReceiptManagement::class)
            ->call('openCreateModal', $this->goodsDonation->id)
            ->set('receiptShelterId', $this->shelter->id)
            ->set('qtyGood', 48)
            ->set('qtyDamaged', 2)
            ->call('saveReceipt')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('donation_receipts', [
            'goods_donation_id' => $this->goodsDonation->id,
            'qty_good' => 48,
            'qty_damaged' => 2,
        ]);

        $this->assertEquals('diterima', $this->goodsDonation->fresh()->status);
    }

    /**
     * Test 7 (AC 2 Lihat): Tabel menampilkan data donasi, filter, dan modal detail
     */
    public function test_can_view_receipt_list_and_detail_modal(): void
    {
        $service = app(DonationReceiptService::class);
        $receipt = $service->recordReceipt(
            $this->goodsDonation,
            $this->shelter->id,
            50,
            0,
            $this->coordinator
        );

        Livewire::actingAs($this->coordinator)
            ->test(DonationReceiptManagement::class)
            ->assertSee('GDN-2611-TEST1')
            ->assertSee('Air Mineral Botol 600ml')
            ->assertSee('Baik: 50 dus')
            ->call('openDetailModal', $receipt->id)
            ->assertSet('showDetailModal', true)
            ->assertSee('Donatur Dermawan')
            ->assertSee('Mutasi Stok Gudang Terkait');
    }

    /**
     * Test 8 (AC 3 Ubah): Koordinator dapat mengoreksi jumlah penerimaan selama belum disalurkan
     * - Stok dan jumlah terpenuhi disesuaikan otomatis
     * - Tercatat mutasi penyesuaian
     */
    public function test_coordinator_can_update_receipt_and_adjust_stock(): void
    {
        $service = app(DonationReceiptService::class);
        $receipt = $service->recordReceipt(
            $this->goodsDonation,
            $this->shelter->id,
            40,
            10,
            $this->coordinator
        );

        $this->assertEquals(40, Stock::where('shelter_id', $this->shelter->id)->where('item_id', $this->item->id)->first()->quantity);
        $this->assertEquals(40, $this->aidNeed->fresh()->qty_fulfilled);

        // Koreksi dari 40 baik menjadi 48 baik (selisih +8)
        $service->updateReceipt($receipt, 48, 2, $this->coordinator, 'Koreksi hitung ulang');

        $this->assertEquals(48, Stock::where('shelter_id', $this->shelter->id)->where('item_id', $this->item->id)->first()->quantity);
        $this->assertEquals(48, $this->aidNeed->fresh()->qty_fulfilled);
        $this->assertEquals(48, $receipt->fresh()->qty_good);
        $this->assertEquals(2, $receipt->fresh()->qty_damaged);

        // Cek tercatat mutasi penyesuaian +8
        $adjustMovement = StockMovement::where('donation_receipt_id', $receipt->id)->where('type', 'penyesuaian')->first();
        $this->assertNotNull($adjustMovement);
        $this->assertEquals(8, $adjustMovement->quantity);
    }

    /**
     * Test 9 (AC 3 Ubah): Pengurangan jumlah baik dilarang jika stok sudah disalurkan
     */
    public function test_cannot_reduce_receipt_qty_if_stock_already_dispatched(): void
    {
        $service = app(DonationReceiptService::class);
        $receipt = $service->recordReceipt(
            $this->goodsDonation,
            $this->shelter->id,
            50,
            0,
            $this->coordinator
        );

        // Simulasi stok sudah disalurkan 45 dus (sisa stok tinggal 5)
        $stock = Stock::where('shelter_id', $this->shelter->id)->where('item_id', $this->item->id)->first();
        $stock->update(['quantity' => 5]);

        // Mencoba mengurangi penerimaan dari 50 menjadi 30 (selisih -20, padahal stok tinggal 5)
        $this->expectException(\InvalidArgumentException::class);
        $service->updateReceipt($receipt, 30, 0, $this->coordinator);
    }

    /**
     * Test 10 (AC 3 Ubah): Penerimaan tidak dapat diubah jika status donasi sudah 'disalurkan'
     */
    public function test_cannot_update_receipt_if_donation_status_is_disalurkan(): void
    {
        $service = app(DonationReceiptService::class);
        $receipt = $service->recordReceipt(
            $this->goodsDonation,
            $this->shelter->id,
            50,
            0,
            $this->coordinator
        );

        $this->goodsDonation->update(['status' => 'disalurkan']);

        $this->expectException(\InvalidArgumentException::class);
        $service->updateReceipt($receipt, 45, 5, $this->coordinator);
    }

    /**
     * Test 11 (AC 4 Hapus): Pembatalan penerimaan donasi
     * - Alasan wajib diisi
     * - Stok dikurangi kembali
     * - Status donasi kembali menjadi 'Dijanjikan'
     * - Jumlah terpenuhi pada kebutuhan berkurang
     */
    public function test_coordinator_can_cancel_receipt_with_reason(): void
    {
        $service = app(DonationReceiptService::class);
        $receipt = $service->recordReceipt(
            $this->goodsDonation,
            $this->shelter->id,
            50,
            0,
            $this->coordinator
        );

        $this->assertEquals(50, Stock::where('shelter_id', $this->shelter->id)->where('item_id', $this->item->id)->first()->quantity);
        $this->assertEquals('diterima', $this->goodsDonation->fresh()->status);

        // Batalkan penerimaan
        $service->cancelReceipt($receipt, 'Salah catat posko tujuan pengiriman', $this->coordinator);

        // Stok berkurang kembali menjadi 0
        $this->assertEquals(0, Stock::where('shelter_id', $this->shelter->id)->where('item_id', $this->item->id)->first()->quantity);

        // Kebutuhan terpenuhi berkurang kembali
        $this->assertEquals(0, $this->aidNeed->fresh()->qty_fulfilled);

        // Status donasi kembali 'dijanjikan'
        $this->assertEquals('dijanjikan', $this->goodsDonation->fresh()->status);

        // Receipt memiliki cancel_reason dan soft-deleted
        $this->assertSoftDeleted('donation_receipts', ['id' => $receipt->id]);
        $this->assertEquals('Salah catat posko tujuan pengiriman', DonationReceipt::withTrashed()->find($receipt->id)->cancel_reason);
    }

    /**
     * Test 12 (AC 4 Hapus): Pembatalan gagal jika tanpa alasan
     */
    public function test_cancel_receipt_requires_reason(): void
    {
        $service = app(DonationReceiptService::class);
        $receipt = $service->recordReceipt(
            $this->goodsDonation,
            $this->shelter->id,
            50,
            0,
            $this->coordinator
        );

        $this->expectException(\InvalidArgumentException::class);
        $service->cancelReceipt($receipt, '   ', $this->coordinator);
    }

    /**
     * Test 13 (AC 4 Hapus): Pembatalan dilarang jika barang sudah disalurkan
     */
    public function test_cannot_cancel_receipt_if_goods_already_dispatched(): void
    {
        $service = app(DonationReceiptService::class);
        $receipt = $service->recordReceipt(
            $this->goodsDonation,
            $this->shelter->id,
            50,
            0,
            $this->coordinator
        );

        // Sisa stok tinggal 10 (40 telah disalurkan)
        $stock = Stock::where('shelter_id', $this->shelter->id)->where('item_id', $this->item->id)->first();
        $stock->update(['quantity' => 10]);

        $this->expectException(\InvalidArgumentException::class);
        $service->cancelReceipt($receipt, 'Batal karena rusak', $this->coordinator);
    }

    /**
     * Test 14 (AC 4 Hapus): Livewire pembatalan penerimaan donasi
     */
    public function test_livewire_can_cancel_receipt(): void
    {
        $service = app(DonationReceiptService::class);
        $receipt = $service->recordReceipt(
            $this->goodsDonation,
            $this->shelter->id,
            50,
            0,
            $this->coordinator
        );

        Livewire::actingAs($this->coordinator)
            ->test(DonationReceiptManagement::class)
            ->call('openCancelModal', $receipt->id)
            ->assertSet('showCancelModal', true)
            ->set('cancelReason', 'Donasi fisik ternyata ditujukan ke posko lain')
            ->call('confirmCancel')
            ->assertHasNoErrors()
            ->assertSet('showCancelModal', false);

        $this->assertEquals('dijanjikan', $this->goodsDonation->fresh()->status);
        $this->assertSoftDeleted('donation_receipts', ['id' => $receipt->id]);
    }
}
