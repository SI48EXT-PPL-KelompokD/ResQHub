<?php

namespace App\Livewire\Admin;

use App\Models\DonationReceipt;
use App\Models\GoodsDonation;
use App\Models\Item;
use App\Models\Shelter;
use App\Services\DonationReceiptService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Penerimaan Donasi Barang - ResQHub')]
class DonationReceiptManagement extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    // Filter & Search
    public string $search = '';
    public string $shelterFilter = '';
    public string $itemFilter = '';
    public string $statusFilter = 'all'; // 'all', 'diterima', 'dibatalkan', 'disalurkan'
    public string $startDate = '';
    public string $endDate = '';

    // Modals
    public bool $showCreateModal = false;
    public bool $showDetailModal = false;
    public bool $showEditModal = false;
    public bool $showCancelModal = false;

    // Form: Catat Penerimaan Donasi (Tambah)
    public string $donationSearch = '';
    public ?int $selectedDonationId = null;
    public ?GoodsDonation $selectedDonation = null;
    public string|int $receiptShelterId = '';
    public string|int $qtyGood = '';
    public string|int $qtyDamaged = 0;
    public string $receivedAt = '';

    // Form: Detail Penerimaan (Lihat)
    public ?int $detailReceiptId = null;
    public ?DonationReceipt $detailReceipt = null;

    // Form: Koreksi Penerimaan (Ubah)
    public ?int $editReceiptId = null;
    public ?DonationReceipt $editReceipt = null;
    public string|int $editQtyGood = '';
    public string|int $editQtyDamaged = '';
    public string $editReason = '';

    // Form: Pembatalan Penerimaan (Hapus)
    public ?int $cancelReceiptId = null;
    public ?DonationReceipt $cancelReceipt = null;
    public string $cancelReason = '';

    // Flash Notification
    public ?array $notification = null;

    public function mount(): void
    {
        $this->receivedAt = now()->format('Y-m-d\TH:i');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingShelterFilter(): void
    {
        $this->resetPage();
    }

    public function updatingItemFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingEndDate(): void
    {
        $this->resetPage();
    }

    /**
     * Modal Catat Penerimaan Baru
     */
    public function openCreateModal(?int $donationId = null): void
    {
        $this->resetCreateForm();
        $this->receivedAt = now()->format('Y-m-d\TH:i');

        if ($donationId) {
            $this->selectDonation($donationId);
        } else {
            // Auto-select donasi pertama yang tersedia agar form langsung terisi otomatis
            $user = Auth::user();
            $query = GoodsDonation::where('status', 'dijanjikan');
            if ($user && !$user->isAdmin()) {
                $shelterIds = $user->coordinatedShelters()->pluck('id')->toArray();
                $query->whereHas('aidNeed', fn ($q) => $q->whereIn('shelter_id', $shelterIds));
            }
            $first = $query->first();
            if ($first) {
                $this->selectDonation($first->id);
            }
        }

        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    public function selectDonation(int $donationId): void
    {
        $donation = GoodsDonation::with(['donor', 'aidNeed.item', 'aidNeed.shelter'])->find($donationId);
        if ($donation && $donation->isDijanjikan()) {
            $this->selectedDonationId = $donation->id;
            $this->selectedDonation = $donation;
            $this->receiptShelterId = $donation->aidNeed?->shelter_id ?? '';
            $this->qtyGood = $donation->quantity;
            $this->qtyDamaged = 0;
        }
    }

    public function resetCreateForm(): void
    {
        $this->donationSearch = '';
        $this->selectedDonationId = null;
        $this->selectedDonation = null;
        $this->receiptShelterId = '';
        $this->qtyGood = '';
        $this->qtyDamaged = 0;
        $this->resetErrorBag();
    }

    public function saveReceipt(DonationReceiptService $service): void
    {
        $this->validate([
            'selectedDonationId' => 'required|exists:goods_donations,id',
            'receiptShelterId' => 'required|exists:shelters,id',
            'qtyGood' => 'required|integer|min:0',
            'qtyDamaged' => 'required|integer|min:0',
            'receivedAt' => 'required|date',
        ], [
            'selectedDonationId.required' => 'Pilih donasi barang yang akan dicatat penerimaannya.',
            'receiptShelterId.required' => 'Pilih posko penerima.',
            'qtyGood.required' => 'Jumlah barang kondisi baik wajib diisi.',
            'qtyGood.min' => 'Jumlah barang baik tidak boleh bernilai negatif.',
            'qtyDamaged.required' => 'Jumlah barang rusak wajib diisi (minimal 0).',
            'qtyDamaged.min' => 'Jumlah barang rusak tidak boleh bernilai negatif.',
            'receivedAt.required' => 'Waktu penerimaan wajib diisi.',
        ]);

        if (((int) $this->qtyGood + (int) $this->qtyDamaged) <= 0) {
            throw ValidationException::withMessages([
                'qtyGood' => 'Total barang yang diterima (baik + rusak) harus lebih dari 0.',
            ]);
        }

        $donation = GoodsDonation::findOrFail($this->selectedDonationId);

        try {
            $receipt = $service->recordReceipt(
                $donation,
                (int) $this->receiptShelterId,
                (int) $this->qtyGood,
                (int) $this->qtyDamaged,
                Auth::user(),
                Carbon::parse($this->receivedAt)
            );

            $this->closeCreateModal();
            $this->notify(
                'success',
                "Penerimaan donasi {$donation->code} berhasil dicatat. Stok posko bertambah {$receipt->qty_good} unit dan notifikasi telah dikirim ke donatur."
            );
        } catch (\Exception $e) {
            $this->notify('error', 'Gagal mencatat penerimaan: ' . $e->getMessage());
        }
    }

    /**
     * Modal Detail Penerimaan
     */
    public function openDetailModal(int $receiptId): void
    {
        $this->detailReceiptId = $receiptId;
        $this->detailReceipt = DonationReceipt::withTrashed()
            ->with([
                'goodsDonation.donor',
                'goodsDonation.aidNeed.item',
                'shelter.disasterEvent',
                'receiver',
                'stockMovements',
            ])
            ->findOrFail($receiptId);

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailReceiptId = null;
        $this->detailReceipt = null;
    }

    /**
     * Modal Koreksi Penerimaan (Ubah)
     */
    public function openEditModal(int $receiptId): void
    {
        $this->editReceiptId = $receiptId;
        $this->editReceipt = DonationReceipt::with(['goodsDonation.aidNeed.item', 'shelter'])->findOrFail($receiptId);

        if (!$this->editReceipt->canBeModifiedOrCancelled()) {
            $this->notify('error', 'Penerimaan ini tidak dapat diubah karena barang sudah disalurkan atau penerimaan telah dibatalkan.');
            return;
        }

        $this->editQtyGood = $this->editReceipt->qty_good;
        $this->editQtyDamaged = $this->editReceipt->qty_damaged;
        $this->editReason = '';
        $this->resetErrorBag();

        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editReceiptId = null;
        $this->editReceipt = null;
        $this->editQtyGood = '';
        $this->editQtyDamaged = '';
        $this->editReason = '';
        $this->resetErrorBag();
    }

    public function updateReceipt(DonationReceiptService $service): void
    {
        $this->validate([
            'editQtyGood' => 'required|integer|min:0',
            'editQtyDamaged' => 'required|integer|min:0',
        ], [
            'editQtyGood.required' => 'Jumlah barang kondisi baik wajib diisi.',
            'editQtyGood.min' => 'Jumlah barang baik tidak boleh bernilai negatif.',
            'editQtyDamaged.required' => 'Jumlah barang rusak wajib diisi.',
            'editQtyDamaged.min' => 'Jumlah barang rusak tidak boleh bernilai negatif.',
        ]);

        if (((int) $this->editQtyGood + (int) $this->editQtyDamaged) <= 0) {
            throw ValidationException::withMessages([
                'editQtyGood' => 'Total barang (baik + rusak) harus lebih dari 0.',
            ]);
        }

        if (!$this->editReceipt) {
            return;
        }

        try {
            $service->updateReceipt(
                $this->editReceipt,
                (int) $this->editQtyGood,
                (int) $this->editQtyDamaged,
                Auth::user(),
                $this->editReason ?: 'Koreksi jumlah penerimaan fisik'
            );

            $this->closeEditModal();
            $this->notify('success', 'Catatan penerimaan donasi berhasil dikoreksi dan stok disesuaikan otomatis.');
        } catch (\Exception $e) {
            $this->notify('error', 'Gagal mengubah penerimaan: ' . $e->getMessage());
        }
    }

    /**
     * Modal Pembatalan Penerimaan (Hapus)
     */
    public function openCancelModal(int $receiptId): void
    {
        $this->cancelReceiptId = $receiptId;
        $this->cancelReceipt = DonationReceipt::with(['goodsDonation.aidNeed.item', 'shelter'])->findOrFail($receiptId);

        if (!$this->cancelReceipt->canBeModifiedOrCancelled()) {
            $this->notify('error', 'Penerimaan ini tidak dapat dibatalkan karena barang sudah disalurkan atau telah dibatalkan.');
            return;
        }

        $this->cancelReason = '';
        $this->resetErrorBag();
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->cancelReceiptId = null;
        $this->cancelReceipt = null;
        $this->cancelReason = '';
        $this->resetErrorBag();
    }

    public function confirmCancel(DonationReceiptService $service): void
    {
        $this->validate([
            'cancelReason' => 'required|string|min:5|max:255',
        ], [
            'cancelReason.required' => 'Alasan pembatalan penerimaan wajib diisi.',
            'cancelReason.min' => 'Alasan pembatalan minimal 5 karakter.',
            'cancelReason.max' => 'Alasan pembatalan maksimal 255 karakter.',
        ]);

        if (!$this->cancelReceipt) {
            return;
        }

        try {
            $code = $this->cancelReceipt->goodsDonation?->code ?? 'Donasi';
            $service->cancelReceipt($this->cancelReceipt, $this->cancelReason, Auth::user());

            $this->closeCancelModal();
            $this->notify('success', "Penerimaan {$code} berhasil dibatalkan. Stok dikurangi kembali dan status donasi kembali 'Dijanjikan'.");
        } catch (\Exception $e) {
            $this->notify('error', 'Gagal membatalkan penerimaan: ' . $e->getMessage());
        }
    }

    public function notify(string $type, string $message): void
    {
        $this->notification = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public function dismissNotification(): void
    {
        $this->notification = null;
    }

    public function render(DonationReceiptService $service): View
    {
        $user = Auth::user();
        $userShelters = $service->getSheltersForUser($user);
        $userShelterIds = $userShelters->pluck('id')->toArray();

        // Query Utama Penerimaan Donasi
        $query = DonationReceipt::withTrashed()
            ->with([
                'goodsDonation.donor',
                'goodsDonation.aidNeed.item',
                'shelter',
                'receiver',
            ]);

        // Filter Posko Akses (Koordinator Posko hanya melihat posko yang dikoordinasikan)
        if (!$user->isAdmin()) {
            $query->whereIn('shelter_id', $userShelterIds);
        }

        // Filter Posko spesifik jika dipilih
        if ($this->shelterFilter !== '') {
            $query->where('shelter_id', $this->shelterFilter);
        }

        // Filter Barang jika dipilih
        if ($this->itemFilter !== '') {
            $query->whereHas('goodsDonation.aidNeed', function ($q) {
                $q->where('item_id', $this->itemFilter);
            });
        }

        // Filter Status
        if ($this->statusFilter === 'diterima') {
            $query->whereNull('deleted_at')
                ->whereNull('cancel_reason')
                ->whereHas('goodsDonation', fn ($q) => $q->where('status', 'diterima'));
        } elseif ($this->statusFilter === 'dibatalkan') {
            $query->where(function ($q) {
                $q->whereNotNull('deleted_at')
                    ->orWhereNotNull('cancel_reason');
            });
        } elseif ($this->statusFilter === 'disalurkan') {
            $query->whereNull('deleted_at')
                ->whereHas('goodsDonation', fn ($q) => $q->where('status', 'disalurkan'));
        }

        // Filter Tanggal
        if ($this->startDate !== '') {
            $query->whereDate('received_at', '>=', $this->startDate);
        }
        if ($this->endDate !== '') {
            $query->whereDate('received_at', '<=', $this->endDate);
        }

        // Pencarian Teks
        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('goodsDonation', function ($sub) use ($term) {
                    $sub->where('code', 'like', $term)
                        ->orWhereHas('donor', fn ($d) => $d->where('name', 'like', $term));
                })->orWhereHas('goodsDonation.aidNeed.item', function ($sub) use ($term) {
                    $sub->where('name', 'like', $term);
                })->orWhereHas('shelter', function ($sub) use ($term) {
                    $sub->where('name', 'like', $term);
                });
            });
        }

        $receipts = $query->latest('received_at')->paginate(10);

        // Statistik / KPI Cards
        $statsBaseQuery = DonationReceipt::withTrashed();
        if (!$user->isAdmin()) {
            $statsBaseQuery->whereIn('shelter_id', $userShelterIds);
        }

        $totalReceipts = (clone $statsBaseQuery)->whereNull('deleted_at')->whereNull('cancel_reason')->count();
        $totalGood = (clone $statsBaseQuery)->whereNull('deleted_at')->whereNull('cancel_reason')->sum('qty_good');
        $totalDamaged = (clone $statsBaseQuery)->whereNull('deleted_at')->whereNull('cancel_reason')->sum('qty_damaged');

        // Total donasi berstatus dijanjikan yang siap diterima
        $pendingDonationsQuery = GoodsDonation::where('status', 'dijanjikan');
        if (!$user->isAdmin()) {
            $pendingDonationsQuery->whereHas('aidNeed', function ($q) use ($userShelterIds) {
                $q->whereIn('shelter_id', $userShelterIds);
            });
        }
        $totalPendingDonations = $pendingDonationsQuery->count();

        // Daftar donasi berstatus dijanjikan untuk modal create
        $availableDonationsQuery = GoodsDonation::with(['donor', 'aidNeed.item', 'aidNeed.shelter'])
            ->where('status', 'dijanjikan');

        if (!$user->isAdmin()) {
            $availableDonationsQuery->whereHas('aidNeed', function ($q) use ($userShelterIds) {
                $q->whereIn('shelter_id', $userShelterIds);
            });
        }

        if (trim($this->donationSearch) !== '') {
            $dTerm = '%' . trim($this->donationSearch) . '%';
            $availableDonationsQuery->where(function ($q) use ($dTerm) {
                $q->where('code', 'like', $dTerm)
                    ->orWhereHas('donor', fn ($d) => $d->where('name', 'like', $dTerm))
                    ->orWhereHas('aidNeed.item', fn ($i) => $i->where('name', 'like', $dTerm));
            });
        }

        $availableDonations = $availableDonationsQuery->latest()->take(20)->get();

        $items = Item::orderBy('name')->get();

        return view('livewire.admin.donation-receipt-management', [
            'receipts' => $receipts,
            'userShelters' => $userShelters,
            'items' => $items,
            'availableDonations' => $availableDonations,
            'stats' => [
                'total_receipts' => $totalReceipts,
                'total_good' => $totalGood,
                'total_damaged' => $totalDamaged,
                'total_pending' => $totalPendingDonations,
            ],
        ]);
    }
}

