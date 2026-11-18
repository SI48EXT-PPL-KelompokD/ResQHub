<?php

namespace App\Livewire\Admin;

use App\Models\AidNeed;
use App\Models\Item;
use App\Models\Shelter;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Kelola Stok Logistik Posko - ResQHub')]
class StockManagement extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    // Filter & Search
    public string $search = '';
    public string $shelterFilter = '';
    public string $categoryFilter = '';
    public string $statusFilter = ''; // 'all', 'menipis', 'habis', 'aman'

    // Modals
    public bool $showIncomingModal = false;
    public bool $showAdjustModal = false;
    public bool $showDeleteModal = false;
    public bool $showHistoryModal = false;
    public bool $showNeedModal = false;

    // Form: Tambah Stok Masuk / Stok Awal
    public string|int $incomingShelterId = '';
    public string|int $incomingItemId = '';
    public string|int $incomingQuantity = '';
    public string $incomingSource = 'Stok Opname';
    public string|int $incomingMinThreshold = 10;
    public string $incomingNotes = '';

    // Form: Penyesuaian Stok (Ubah)
    public ?int $selectedStockId = null;
    public ?Stock $selectedStock = null;
    public string|int $adjustNewQuantity = '';
    public string $adjustReason = '';
    public string|int $adjustMinThreshold = '';

    // Form: Hapus Stok
    public ?int $deleteStockId = null;
    public ?Stock $deleteStock = null;
    public ?string $deleteBlockReason = null;

    // Form: Riwayat Mutasi
    public ?int $historyStockId = null;
    public ?Stock $historyStock = null;

    // Form: Ajukan Kebutuhan (PBI-11)
    public string|int $needShelterId = '';
    public string|int $needItemId = '';
    public string|int $needQty = '';
    public string $needPriority = 'normal';
    public ?string $needDeadline = null;

    // Flash Notification
    public ?array $notification = null;

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user || ! $user->canAccessModule('modul_f')) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengelola stok logistik posko.');
        }

        // Tentukan default shelterFilter untuk koordinator
        if ($user->isKoordinator()) {
            $firstShelter = Shelter::where('coordinator_id', $user->id)->first();
            if ($firstShelter) {
                $this->shelterFilter = (string) $firstShelter->id;
                $this->incomingShelterId = $firstShelter->id;
            }
        } else {
            $firstShelter = Shelter::first();
            if ($firstShelter) {
                $this->incomingShelterId = $firstShelter->id;
            }
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingShelterFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Dapatkan daftar posko yang boleh diakses pengguna.
     */
    public function getAvailableSheltersProperty()
    {
        $stockService = app(StockService::class);
        return $stockService->getSheltersForUser(Auth::user());
    }

    /**
     * Dapatkan daftar katalog barang aktif.
     */
    public function getAvailableItemsProperty()
    {
        return Item::where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Buka modal pencatatan stok awal / barang masuk.
     */
    public function openIncomingModal(): void
    {
        $this->resetValidation();
        $this->incomingQuantity = '';
        $this->incomingSource = 'Stok Opname';
        $this->incomingNotes = '';

        if (! $this->incomingShelterId && $this->availableShelters->isNotEmpty()) {
            $this->incomingShelterId = $this->availableShelters->first()->id;
        }

        if (! $this->incomingItemId && $this->availableItems->isNotEmpty()) {
            $this->incomingItemId = $this->availableItems->first()->id;
            $this->incomingMinThreshold = $this->availableItems->first()->min_threshold ?? 10;
        }

        $this->showIncomingModal = true;
    }

    public function closeIncomingModal(): void
    {
        $this->showIncomingModal = false;
        $this->resetValidation();
    }

    /**
     * Ketika item di form tambah berubah, sinkronkan threshold defaultnya.
     */
    public function updatedIncomingItemId($itemId): void
    {
        $item = Item::find($itemId);
        if ($item) {
            $this->incomingMinThreshold = $item->min_threshold ?? 10;
        }
    }

    /**
     * Simpan stok awal / barang masuk di luar donasi.
     */
    public function saveIncomingStock(StockService $stockService): void
    {
        $this->validate([
            'incomingShelterId' => ['required', 'exists:shelters,id'],
            'incomingItemId' => ['required', 'exists:items,id'],
            'incomingQuantity' => ['required', 'integer', 'min:1'],
            'incomingSource' => ['required', 'string', 'max:100'],
            'incomingMinThreshold' => ['required', 'integer', 'min:0'],
            'incomingNotes' => ['nullable', 'string', 'max:255'],
        ], [
            'incomingShelterId.required' => 'Posko wajib dipilih.',
            'incomingItemId.required' => 'Barang katalog wajib dipilih.',
            'incomingQuantity.required' => 'Jumlah barang masuk wajib diisi.',
            'incomingQuantity.min' => 'Jumlah barang masuk minimal 1.',
            'incomingSource.required' => 'Sumber barang wajib diisi.',
            'incomingMinThreshold.required' => 'Ambang menipis wajib diisi.',
        ]);

        // Cek otorisasi posko untuk koordinator
        $user = Auth::user();
        if ($user->isKoordinator()) {
            $isOwner = Shelter::where('id', $this->incomingShelterId)
                ->where('coordinator_id', $user->id)
                ->exists();
            if (! $isOwner) {
                abort(403, 'Anda tidak berwenang mengelola stok di posko ini.');
            }
        }

        try {
            $stockService->recordIncomingStock([
                'shelter_id' => $this->incomingShelterId,
                'item_id' => $this->incomingItemId,
                'quantity' => (int) $this->incomingQuantity,
                'source' => $this->incomingSource,
                'min_threshold' => (int) $this->incomingMinThreshold,
                'notes' => $this->incomingNotes,
            ], $user);

            $this->showIncomingModal = false;
            $this->notification = [
                'type' => 'success',
                'message' => 'Stok barang masuk berhasil dicatat dan tercatat sebagai mutasi masuk.',
            ];
        } catch (\Throwable $e) {
            $this->addError('incoming', $e->getMessage());
        }
    }

    /**
     * Buka modal penyesuaian stok (koreksi fisik / barang rusak / kedaluwarsa).
     */
    public function openAdjustModal(int $stockId): void
    {
        $this->resetValidation();
        $this->selectedStockId = $stockId;
        $this->selectedStock = Stock::with(['shelter', 'item'])->findOrFail($stockId);

        $this->adjustNewQuantity = $this->selectedStock->quantity;
        $this->adjustMinThreshold = $this->selectedStock->min_threshold;
        $this->adjustReason = '';

        $this->showAdjustModal = true;
    }

    public function closeAdjustModal(): void
    {
        $this->showAdjustModal = false;
        $this->selectedStockId = null;
        $this->selectedStock = null;
        $this->resetValidation();
    }

    /**
     * Simpan penyesuaian stok.
     */
    public function saveAdjustment(StockService $stockService): void
    {
        $this->validate([
            'adjustNewQuantity' => ['required', 'integer', 'min:0'],
            'adjustReason' => ['required', 'string', 'min:3', 'max:255'],
            'adjustMinThreshold' => ['required', 'integer', 'min:0'],
        ], [
            'adjustNewQuantity.required' => 'Jumlah stok fisik baru wajib diisi.',
            'adjustNewQuantity.min' => 'Jumlah stok tidak boleh kurang dari 0.',
            'adjustReason.required' => 'Alasan penyesuaian wajib diisi (contoh: koreksi hitung fisik, barang rusak, kedaluwarsa).',
            'adjustReason.min' => 'Alasan penyesuaian minimal 3 karakter.',
            'adjustMinThreshold.required' => 'Ambang menipis wajib diisi.',
        ]);

        $stock = Stock::findOrFail($this->selectedStockId);

        // Otorisasi posko
        $user = Auth::user();
        if ($user->isKoordinator() && $stock->shelter->coordinator_id !== $user->id) {
            abort(403, 'Anda tidak berwenang mengubah stok di posko ini.');
        }

        try {
            $stockService->adjustStock(
                $stock,
                (int) $this->adjustNewQuantity,
                $this->adjustReason,
                (int) $this->adjustMinThreshold,
                $user
            );

            $this->showAdjustModal = false;
            $this->selectedStockId = null;
            $this->selectedStock = null;

            $this->notification = [
                'type' => 'success',
                'message' => 'Penyesuaian stok berhasil disimpan dan selisih tercatat pada riwayat mutasi.',
            ];
        } catch (ValidationException $ve) {
            foreach ($ve->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
        } catch (\Throwable $e) {
            $this->addError('adjust', $e->getMessage());
        }
    }

    /**
     * Buka modal konfirmasi hapus stok.
     */
    public function openDeleteModal(int $stockId): void
    {
        $this->deleteStockId = $stockId;
        $this->deleteStock = Stock::with(['shelter', 'item'])->findOrFail($stockId);

        $this->deleteBlockReason = null;

        if ($this->deleteStock->quantity > 0) {
            $this->deleteBlockReason = "Stok masih memiliki {$this->deleteStock->quantity} {$this->deleteStock->item->unit}. Hanya stok dengan jumlah 0 yang dapat dihapus.";
        } elseif ($this->deleteStock->hasOpenNeeds()) {
            $this->deleteBlockReason = "Posko ini masih memiliki pengajuan kebutuhan terbuka untuk barang ini. Tutup atau penuhi kebutuhan bantuan terlebih dahulu.";
        }

        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteStockId = null;
        $this->deleteStock = null;
        $this->deleteBlockReason = null;
    }

    /**
     * Hapus stok logistik.
     */
    public function deleteStockAction(StockService $stockService): void
    {
        if (! $this->deleteStockId) {
            return;
        }

        $stock = Stock::findOrFail($this->deleteStockId);

        // Otorisasi posko
        $user = Auth::user();
        if ($user->isKoordinator() && $stock->shelter->coordinator_id !== $user->id) {
            abort(403, 'Anda tidak berwenang menghapus stok di posko ini.');
        }

        try {
            $stockService->deleteStock($stock);

            $this->showDeleteModal = false;
            $this->deleteStockId = null;
            $this->deleteStock = null;

            $this->notification = [
                'type' => 'success',
                'message' => 'Baris stok berhasil dihapus dari tabel gudang posko. Seluruh riwayat mutasi tetap tersimpan aman.',
            ];
        } catch (ValidationException $ve) {
            foreach ($ve->errors() as $messages) {
                $this->deleteBlockReason = $messages[0];
            }
        } catch (\Throwable $e) {
            $this->deleteBlockReason = $e->getMessage();
        }
    }

    /**
     * Buka modal riwayat mutasi untuk barang tertentu di posko.
     */
    public function openHistoryModal(int $stockId): void
    {
        $this->historyStockId = $stockId;
        $this->historyStock = Stock::withTrashed()->with(['shelter', 'item', 'movements.creator'])->findOrFail($stockId);
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal(): void
    {
        $this->showHistoryModal = false;
        $this->historyStockId = null;
        $this->historyStock = null;
    }

    /**
     * Buka modal Ajukan Kebutuhan (PBI-11) dengan barang & posko terisi otomatis.
     */
    public function openNeedModal(int $stockId): void
    {
        $this->resetValidation();
        $stock = Stock::with(['shelter', 'item'])->findOrFail($stockId);

        $this->needShelterId = $stock->shelter_id;
        $this->needItemId = $stock->item_id;
        $this->needQty = max(1, $stock->min_threshold - $stock->quantity);
        $this->needPriority = $stock->quantity <= 0 ? 'mendesak' : ($stock->isDepleted() ? 'tinggi' : 'normal');
        $this->needDeadline = Carbon::now()->addDays(3)->format('Y-m-d');

        $this->showNeedModal = true;
    }

    public function closeNeedModal(): void
    {
        $this->showNeedModal = false;
        $this->resetValidation();
    }

    /**
     * Simpan pengajuan kebutuhan bantuan posko (PBI-11).
     */
    public function submitAidNeed(): void
    {
        $this->validate([
            'needShelterId' => ['required', 'exists:shelters,id'],
            'needItemId' => ['required', 'exists:items,id'],
            'needQty' => ['required', 'integer', 'min:1'],
            'needPriority' => ['required', 'in:mendesak,tinggi,normal'],
            'needDeadline' => ['nullable', 'date'],
        ], [
            'needQty.required' => 'Jumlah kebutuhan bantuan wajib diisi.',
            'needQty.min' => 'Jumlah kebutuhan minimal 1.',
        ]);

        AidNeed::create([
            'shelter_id' => $this->needShelterId,
            'item_id' => $this->needItemId,
            'qty_needed' => (int) $this->needQty,
            'qty_pledged' => 0,
            'qty_fulfilled' => 0,
            'priority' => $this->needPriority,
            'deadline' => $this->needDeadline,
            'status' => 'terbuka',
        ]);

        $this->showNeedModal = false;
        $this->notification = [
            'type' => 'success',
            'message' => 'Pengajuan kebutuhan bantuan posko (PBI-11) berhasil dibuat dengan status terbuka.',
        ];
    }

    public function dismissNotification(): void
    {
        $this->notification = null;
    }

    public function render()
    {
        $user = Auth::user();

        // Query stok aktif
        $query = Stock::with(['shelter', 'item']);

        // Batasi berdasarkan posko
        if ($user->isKoordinator()) {
            $allowedShelterIds = Shelter::where('coordinator_id', $user->id)->pluck('id')->toArray();
            $query->whereIn('shelter_id', $allowedShelterIds);
        }

        if ($this->shelterFilter) {
            $query->where('shelter_id', $this->shelterFilter);
        }

        // Filter pencarian
        if ($this->search) {
            $search = '%' . trim($this->search) . '%';
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('category', 'like', $search)
                    ->orWhere('unit', 'like', $search);
            });
        }

        // Filter kategori
        if ($this->categoryFilter) {
            $query->whereHas('item', function ($q) {
                $q->where('category', $this->categoryFilter);
            });
        }

        // Filter status
        if ($this->statusFilter === 'menipis') {
            $query->whereColumn('quantity', '<', 'min_threshold')->where('quantity', '>', 0);
        } elseif ($this->statusFilter === 'habis') {
            $query->where('quantity', '<=', 0);
        } elseif ($this->statusFilter === 'aman') {
            $query->whereColumn('quantity', '>=', 'min_threshold');
        }

        // Pengurutan
        $query->orderBy('quantity', 'asc');

        $stocks = $query->paginate(10);

        // Ringkasan statistik cepat
        $baseStatQuery = Stock::query();
        if ($user->isKoordinator()) {
            $baseStatQuery->whereIn('shelter_id', $allowedShelterIds);
        }
        if ($this->shelterFilter) {
            $baseStatQuery->where('shelter_id', $this->shelterFilter);
        }

        $totalItems = (clone $baseStatQuery)->count();
        $totalQty = (clone $baseStatQuery)->sum('quantity');
        $totalDepleted = (clone $baseStatQuery)->where(function ($q) {
            $q->whereColumn('quantity', '<', 'min_threshold');
        })->count();
        $totalEmpty = (clone $baseStatQuery)->where('quantity', '<=', 0)->count();

        return view('livewire.admin.stock-management', [
            'stocks' => $stocks,
            'totalItems' => $totalItems,
            'totalQty' => $totalQty,
            'totalDepleted' => $totalDepleted,
            'totalEmpty' => $totalEmpty,
        ]);
    }
}
