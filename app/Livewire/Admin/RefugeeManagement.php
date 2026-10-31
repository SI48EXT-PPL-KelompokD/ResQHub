<?php

namespace App\Livewire\Admin;

use App\Models\Refugee;
use App\Models\Shelter;
use App\Models\User;
use App\Services\RefugeeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Data Pengungsi - ResQHub')]
class RefugeeManagement extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    // Filters
    public string $shelterFilter = '';
    public string $search = '';
    public string $statusFilter = '';
    public string $sortFilter = 'latest';

    // Panel & Modals state
    public string $panelMode = 'create'; // 'create' | 'edit' | 'detail'
    public bool $showCreateModal = false;
    public bool $showDetailModal = false;
    public bool $showEditModal = false;
    public bool $showTransferModal = false;
    public bool $showCheckoutModal = false;
    public bool $showDeleteModal = false;

    // Form fields
    public ?int $refugeeId = null;
    public string|int $shelter_id = '';
    public string $head_name = '';
    public string $nik = '';
    public int $members = 1;
    public int $infants = 0;
    public int $elderly = 0;
    public int $pregnant = 0;
    public int $disabled = 0;
    public string $statusAction = 'tetap'; // 'tetap' | 'pindah' | 'pulang'
    public string|int $to_shelter_id = '';
    public string $delete_reason = 'Data ganda / salah input';

    // Detail & Warnings
    public ?Refugee $selectedRefugee = null;
    public array $formWarnings = [];
    public ?array $notification = null;

    public function mount(RefugeeService $refugeeService): void
    {
        $user = $this->currentUser();

        if (!$user->canAccessModule('modul_c')) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin mengakses Modul Pengungsi & Kesehatan.');
        }

        $shelters = $refugeeService->getAccessibleShelters($user);
        if ($user->isKoordinator() && $shelters->isNotEmpty()) {
            $this->shelter_id = (int) $shelters->first()->id;
            if ($shelters->count() === 1) {
                $this->shelterFilter = (string) $shelters->first()->id;
            }
        } elseif ($shelters->isNotEmpty()) {
            $this->shelter_id = (int) $shelters->first()->id;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingShelterFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSortFilter(): void
    {
        $this->resetPage();
    }

    public function updatedNik(): void
    {
        $this->refreshFormWarnings();
    }

    public function updatedMembers(): void
    {
        $this->refreshFormWarnings();
    }

    public function updatedShelterId(): void
    {
        $this->refreshFormWarnings();
    }

    public function refreshFormWarnings(): void
    {
        $this->formWarnings = [];

        if (empty($this->shelter_id)) {
            return;
        }

        $shelter = Shelter::find((int) $this->shelter_id);
        if (!$shelter) {
            return;
        }

        $service = app(RefugeeService::class);
        $additionalMembers = (int) $this->members;

        if ($this->refugeeId !== null) {
            $existing = Refugee::find($this->refugeeId);
            if ($existing) {
                $additionalMembers = max(0, (int) $this->members - (int) $existing->members);
            }
        }

        $this->formWarnings = $service->evaluateWarnings(
            $shelter,
            $additionalMembers,
            $this->nik !== '' ? $this->nik : null,
            $this->refugeeId
        );
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        if ($this->shelterFilter !== '') {
            $this->shelter_id = (int) $this->shelterFilter;
        }
        $this->panelMode = 'create';
        $this->showCreateModal = true;
        $this->showEditModal = false;
        $this->showDetailModal = false;
        $this->refreshFormWarnings();
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function checkIn(RefugeeService $refugeeService): void
    {
        $validated = $this->validate([
            'shelter_id' => ['required', 'integer', 'exists:shelters,id'],
            'head_name' => ['required', 'string', 'max:100'],
            'nik' => ['nullable', 'string', 'digits:16'],
            'members' => ['required', 'integer', 'min:1'],
            'infants' => ['nullable', 'integer', 'min:0'],
            'elderly' => ['nullable', 'integer', 'min:0'],
            'pregnant' => ['nullable', 'integer', 'min:0'],
            'disabled' => ['nullable', 'integer', 'min:0'],
        ]);

        $result = $refugeeService->checkIn($this->currentUser(), $validated);

        $this->showCreateModal = false;
        $this->resetForm();

        $warningMessages = array_column($result['warnings'], 'message');
        $this->notify('Data pengungsi berhasil didaftarkan (Check-In).', 'success', $warningMessages);
    }

    public function openDetailModal(int $id, RefugeeService $refugeeService): void
    {
        $this->selectedRefugee = $refugeeService->getRefugeeDetail($this->currentUser(), $id);
        $this->refugeeId = $this->selectedRefugee->id;
        $this->panelMode = 'detail';
        $this->showDetailModal = true;
        $this->showEditModal = false;
        $this->showCreateModal = false;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedRefugee = null;
        $this->refugeeId = null;
        $this->panelMode = 'create';
    }

    public function openEditModal(int $id, RefugeeService $refugeeService): void
    {
        $refugee = $refugeeService->getRefugeeDetail($this->currentUser(), $id);

        $this->selectedRefugee = $refugee;
        $this->refugeeId = $refugee->id;
        $this->shelter_id = $refugee->shelter_id;
        $this->head_name = $refugee->head_name;
        $this->nik = (string) ($refugee->nik ?? '');
        $this->members = (int) $refugee->members;
        $this->infants = (int) $refugee->infants;
        $this->elderly = (int) $refugee->elderly;
        $this->pregnant = (int) $refugee->pregnant;
        $this->disabled = (int) $refugee->disabled;
        $this->statusAction = 'tetap';
        $this->to_shelter_id = '';

        $destinations = $refugeeService->getDestinationShelters($refugee->shelter_id);
        if ($destinations->isNotEmpty()) {
            $this->to_shelter_id = (int) $destinations->first()->id;
        }

        $this->refreshFormWarnings();
        $this->panelMode = 'edit';
        $this->showEditModal = true;
        $this->showDetailModal = false;
        $this->showCreateModal = false;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->panelMode = 'create';
        $this->resetForm();
    }

    public function updateRefugee(RefugeeService $refugeeService): void
    {
        $rules = [
            'head_name' => ['required', 'string', 'max:100'],
            'nik' => ['nullable', 'string', 'digits:16'],
            'members' => ['required', 'integer', 'min:1'],
            'infants' => ['nullable', 'integer', 'min:0'],
            'elderly' => ['nullable', 'integer', 'min:0'],
            'pregnant' => ['nullable', 'integer', 'min:0'],
            'disabled' => ['nullable', 'integer', 'min:0'],
            'statusAction' => ['nullable', 'in:tetap,pindah,pulang'],
        ];

        if ($this->statusAction === 'pindah') {
            $rules['to_shelter_id'] = ['required', 'integer', 'exists:shelters,id'];
        }

        $validated = $this->validate($rules);

        $user = $this->currentUser();
        $refugee = Refugee::findOrFail($this->refugeeId);

        [$message, $warningMessages] = DB::transaction(function () use ($refugeeService, $user, $refugee, $validated) {
            $result = $refugeeService->updateFamilyData($user, $refugee, $validated);
            $warnings = array_column($result['warnings'], 'message');
            $msg = 'Data keluarga pengungsi berhasil diperbarui.';

            if ($this->statusAction === 'pindah') {
                $updated = $refugeeService->transferShelter($user, $result['refugee'], (int) $this->to_shelter_id);
                $msg = "Data diperbarui & perpindahan keluarga {$updated->head_name} ke {$updated->shelter->name} berhasil dicatat.";
            } elseif ($this->statusAction === 'pulang') {
                $updated = $refugeeService->checkOut($user, $result['refugee']);
                $msg = "Data diperbarui & kepulangan keluarga {$updated->head_name} berhasil dicatat.";
            }

            return [$msg, $warnings];
        });

        $this->showEditModal = false;
        $this->panelMode = 'create';
        $this->resetForm();

        $this->notify($message, 'success', $warningMessages);
    }

    public function openCheckoutModal(int $id, RefugeeService $refugeeService): void
    {
        $this->selectedRefugee = $refugeeService->getRefugeeDetail($this->currentUser(), $id);
        $this->refugeeId = $this->selectedRefugee->id;
        $this->showCheckoutModal = true;
    }

    public function closeCheckoutModal(): void
    {
        $this->showCheckoutModal = false;
        $this->selectedRefugee = null;
        $this->refugeeId = null;
    }

    public function confirmCheckout(RefugeeService $refugeeService): void
    {
        $refugee = Refugee::findOrFail($this->refugeeId);
        $updated = $refugeeService->checkOut($this->currentUser(), $refugee);

        $this->closeCheckoutModal();
        $this->notify("Kepulangan keluarga {$updated->head_name} berhasil dicatat dan okupansi posko diperbarui.");
    }

    public function openTransferModal(int $id, RefugeeService $refugeeService): void
    {
        $this->selectedRefugee = $refugeeService->getRefugeeDetail($this->currentUser(), $id);
        $this->refugeeId = $this->selectedRefugee->id;
        $this->to_shelter_id = '';
        $this->resetErrorBag();
        $this->showTransferModal = true;
    }

    public function closeTransferModal(): void
    {
        $this->showTransferModal = false;
        $this->selectedRefugee = null;
        $this->refugeeId = null;
        $this->to_shelter_id = '';
        $this->resetErrorBag();
    }

    public function confirmTransfer(RefugeeService $refugeeService): void
    {
        $this->validate([
            'to_shelter_id' => ['required', 'integer', 'exists:shelters,id'],
        ]);

        $refugee = Refugee::findOrFail($this->refugeeId);
        $updated = $refugeeService->transferShelter($this->currentUser(), $refugee, (int) $this->to_shelter_id);

        $this->closeTransferModal();
        $this->notify("Perpindahan keluarga {$updated->head_name} ke {$updated->shelter->name} berhasil dicatat.");
    }

    public function openDeleteModal(int $id, RefugeeService $refugeeService): void
    {
        $this->selectedRefugee = $refugeeService->getRefugeeDetail($this->currentUser(), $id);
        $this->refugeeId = $this->selectedRefugee->id;
        $this->delete_reason = 'Data ganda / salah input';
        $this->resetErrorBag();
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->delete_reason = 'Data ganda / salah input';
        $this->resetErrorBag();
    }

    public function confirmDelete(RefugeeService $refugeeService): void
    {
        $this->validate([
            'delete_reason' => ['required', 'string', 'max:255'],
        ]);

        $refugee = Refugee::findOrFail($this->refugeeId);

        try {
            $deleted = $refugeeService->deleteRefugee($this->currentUser(), $refugee, $this->delete_reason);
            $this->closeDeleteModal();
            if ($this->panelMode !== 'create') {
                $this->panelMode = 'create';
                $this->resetForm();
            }
            $this->notify("Data pengungsi {$deleted->head_name} berhasil dihapus dan okupansi posko telah dikoreksi.");
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    protected function resetForm(): void
    {
        $this->refugeeId = null;
        $this->selectedRefugee = null;
        $this->head_name = '';
        $this->nik = '';
        $this->members = 1;
        $this->infants = 0;
        $this->elderly = 0;
        $this->pregnant = 0;
        $this->disabled = 0;
        $this->statusAction = 'tetap';
        $this->to_shelter_id = '';
        $this->delete_reason = 'Data ganda / salah input';
        $this->formWarnings = [];
        $this->resetErrorBag();
    }

    /**
     * @param list<string> $warnings
     */
    protected function notify(string $message, string $type = 'success', array $warnings = []): void
    {
        $this->notification = [
            'message' => $message,
            'type' => $type,
            'warnings' => $warnings,
        ];
    }

    public function dismissNotification(): void
    {
        $this->notification = null;
    }

    /**
     * Ambil instance User model yang sedang terotentikasi.
     */
    protected function currentUser(): User
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Sesi autentikasi telah berakhir.');
        }

        return $user;
    }

    public function render(RefugeeService $refugeeService)
    {
        $user = $this->currentUser();
        $selectedShelterId = $this->shelterFilter !== '' ? (int) $this->shelterFilter : null;

        $filters = [
            'shelter_id' => $selectedShelterId,
            'search' => $this->search,
            'status' => $this->statusFilter,
            'sort' => $this->sortFilter,
        ];

        $refugees = $refugeeService->listRefugees($user, $filters, 10);
        $accessibleShelters = $refugeeService->getAccessibleShelters($user);

        $currentShelter = $selectedShelterId
            ? $accessibleShelters->firstWhere('id', $selectedShelterId)
            : ($accessibleShelters->count() === 1 ? $accessibleShelters->first() : null);

        $destinationShelters = $refugeeService->getDestinationShelters(
            $this->selectedRefugee?->shelter_id ?? ($this->shelter_id ? (int) $this->shelter_id : null)
        );

        $statistics = $refugeeService->getSummaryStatistics($user, $selectedShelterId);

        return view('livewire.admin.refugee-management', [
            'refugees' => $refugees,
            'accessibleShelters' => $accessibleShelters,
            'currentShelter' => $currentShelter,
            'destinationShelters' => $destinationShelters,
            'statistics' => $statistics,
            'canManage' => $user->isAdmin() || $user->isKoordinator(),
        ]);
    }
}
