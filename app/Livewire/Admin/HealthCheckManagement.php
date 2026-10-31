<?php

namespace App\Livewire\Admin;

use App\Http\Requests\HealthCheck\StoreHealthCheckRequest;
use App\Models\HealthCheck;
use App\Models\Refugee;
use App\Models\User;
use App\Services\HealthCheckService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Pemeriksaan & Triase - ResQHub')]
class HealthCheckManagement extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    // Filters
    public string $shelterFilter = '';
    public string $shelterSearch = '';
    public string $triageFilter = '';
    public string $dateFilter = '';
    public string $search = '';

    // Panel & Modal state
    public string $panelMode = 'create'; // 'create' | 'edit' | 'history'
    public bool $showDeleteModal = false;

    // Form fields
    public ?int $healthCheckId = null;
    public string|int $refugee_id = '';
    public string $refugeeSearch = '';
    public string $complaint = '';
    public string|float $temperature = '';
    public string $blood_pressure = '';
    public string|int $pulse = '';
    public string $triage = 'hijau';
    public string $action = '';
    public string $delete_reason = 'Salah memilih pengungsi';

    // Selected entities
    public ?HealthCheck $selectedCheck = null;
    public ?Refugee $historyRefugee = null;
    public ?array $notification = null;

    public function mount(HealthCheckService $healthCheckService): void
    {
        $user = $this->currentUser();

        if (! $user->isNakes() && ! $user->isKoordinator() && ! $user->isAdmin()) {
            abort(403, 'Akses Ditolak: Data pemeriksaan hanya dapat diakses Tenaga Kesehatan dan Koordinator Posko terkait.');
        }

        $shelters = $healthCheckService->getAccessibleShelters($user);
        if ($user->isKoordinator() && $shelters->count() === 1) {
            $this->shelterFilter = (string) $shelters->first()->id;
        }

        if (! $user->isNakes()) {
            $this->panelMode = 'history';
        }

        $refugeeParam = (int) request()->query('refugee', 0);
        if ($refugeeParam > 0) {
            $this->openHistoryModal($refugeeParam, $healthCheckService);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTriageFilter(): void
    {
        $this->resetPage();
    }

    public function updatingShelterFilter(): void
    {
        $this->resetPage();
    }

    public function selectShelter(?int $shelterId = null): void
    {
        if ($shelterId === null) {
            $this->shelterFilter = '';
            $this->shelterSearch = '';
        } else {
            $shelter = $this->currentUser()
                ? app(HealthCheckService::class)->getAccessibleShelters($this->currentUser())->firstWhere('id', $shelterId)
                : null;
            $this->shelterFilter = (string) $shelterId;
            $this->shelterSearch = $shelter?->name ?? '';
        }

        $this->resetPage();
    }

    public function selectRefugee(int $refugeeId): void
    {
        $refugee = Refugee::with('shelter')->find($refugeeId);
        if ($refugee) {
            $this->refugee_id = $refugee->id;
            $this->refugeeSearch = $refugee->head_name;
            $this->resetErrorBag('refugee_id');
        }
    }

    public function clearSelectedRefugee(): void
    {
        $this->refugee_id = '';
        $this->refugeeSearch = '';
    }

    public function updatingDateFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(?int $refugeeId = null): void
    {
        $this->ensureNakes();
        $this->resetForm();
        if ($refugeeId !== null) {
            $this->selectRefugee($refugeeId);
        }
        $this->panelMode = 'create';
    }

    public function closeFormPanel(): void
    {
        $this->resetForm();
        $this->panelMode = $this->currentUser()->isNakes() ? 'create' : 'history';
    }

    public function storeHealthCheck(HealthCheckService $healthCheckService): void
    {
        $this->ensureNakes();

        $this->temperature = str_replace(',', '.', (string) $this->temperature);

        $rules = array_merge([
            'refugee_id' => ['required', 'integer', 'exists:refugees,id'],
        ], StoreHealthCheckRequest::vitalRules());
        unset($rules['checked_at']);

        $validated = $this->validate($rules, StoreHealthCheckRequest::vitalMessages());

        $check = $healthCheckService->createHealthCheck($this->currentUser(), $validated);

        $this->resetForm();
        $this->panelMode = 'create';

        $message = $check->isCritical()
            ? "Pemeriksaan {$check->refugee?->head_name} tersimpan. Triase MERAH: koordinator posko telah dinotifikasi."
            : "Catatan pemeriksaan {$check->refugee?->head_name} berhasil disimpan.";

        $this->notify($message, 'success');
    }

    public function openEditModal(int $id, HealthCheckService $healthCheckService): void
    {
        $check = $healthCheckService->getHealthCheckDetail($this->currentUser(), $id);

        if (! $this->currentUser()->isNakes()) {
            $this->openHistoryModal((int) $check->refugee_id, $healthCheckService);

            return;
        }

        $this->selectedCheck = $check;
        $this->healthCheckId = $check->id;
        $this->refugee_id = $check->refugee_id;
        $this->complaint = $check->complaint;
        $this->temperature = (string) $check->temperature;
        $this->blood_pressure = $check->blood_pressure;
        $this->pulse = (int) $check->pulse;
        $this->triage = $check->triage;
        $this->action = (string) ($check->action ?? '');
        $this->resetErrorBag();
        $this->panelMode = 'edit';
    }

    public function updateHealthCheck(HealthCheckService $healthCheckService): void
    {
        $this->ensureNakes();

        $this->temperature = str_replace(',', '.', (string) $this->temperature);

        $rules = StoreHealthCheckRequest::vitalRules();
        unset($rules['checked_at']);

        $validated = $this->validate($rules, StoreHealthCheckRequest::vitalMessages());

        $check = HealthCheck::findOrFail($this->healthCheckId);
        $updated = $healthCheckService->updateHealthCheck($this->currentUser(), $check, $validated);

        $this->resetForm();
        $this->panelMode = 'create';

        $this->notify("Pemeriksaan {$updated->refugee?->head_name} berhasil diperbarui dan tercatat pada log aktivitas.");
    }

    public function openHistoryModal(int $refugeeId, HealthCheckService $healthCheckService): void
    {
        $this->historyRefugee = $healthCheckService->getRefugeeHistory($this->currentUser(), $refugeeId);
        $this->panelMode = 'history';
    }

    public function openDeleteModal(int $id, HealthCheckService $healthCheckService): void
    {
        $this->ensureNakes();
        $this->selectedCheck = $healthCheckService->getHealthCheckDetail($this->currentUser(), $id);
        $this->healthCheckId = $this->selectedCheck->id;
        $this->delete_reason = 'Salah memilih pengungsi';
        $this->resetErrorBag();
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->delete_reason = 'Salah memilih pengungsi';
        $this->resetErrorBag();
    }

    public function confirmDelete(HealthCheckService $healthCheckService): void
    {
        $this->ensureNakes();

        $this->validate([
            'delete_reason' => ['required', 'string', 'max:255'],
        ], [
            'delete_reason.required' => 'Alasan penghapusan catatan pemeriksaan wajib diisi.',
        ]);

        $check = HealthCheck::findOrFail($this->healthCheckId);
        $healthCheckService->deleteHealthCheck($this->currentUser(), $check, $this->delete_reason);

        $this->closeDeleteModal();
        $this->resetForm();
        $this->panelMode = 'create';

        $this->notify('Catatan pemeriksaan yang salah input berhasil dihapus (soft delete).');
    }

    public function dismissNotification(): void
    {
        $this->notification = null;
    }

    protected function resetForm(): void
    {
        $this->healthCheckId = null;
        $this->selectedCheck = null;
        $this->refugee_id = '';
        $this->refugeeSearch = '';
        $this->complaint = '';
        $this->temperature = '';
        $this->blood_pressure = '';
        $this->pulse = '';
        $this->triage = 'hijau';
        $this->action = '';
        $this->delete_reason = 'Salah memilih pengungsi';
        $this->resetErrorBag();
    }

    protected function notify(string $message, string $type = 'success'): void
    {
        $this->notification = [
            'message' => $message,
            'type' => $type,
        ];
    }

    protected function ensureNakes(): void
    {
        if (! $this->currentUser()->isNakes()) {
            abort(403, 'Akses Ditolak: Hanya Tenaga Kesehatan yang dapat mengelola catatan pemeriksaan.');
        }
    }

    protected function currentUser(): User
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Sesi autentikasi telah berakhir.');
        }

        return $user;
    }

    public function render(HealthCheckService $healthCheckService)
    {
        $user = $this->currentUser();
        $selectedShelterId = $this->shelterFilter !== '' ? (int) $this->shelterFilter : null;

        $checks = $healthCheckService->listHealthChecks($user, [
            'shelter_id' => $selectedShelterId,
            'triage' => $this->triageFilter,
            'date' => $this->dateFilter !== '' ? $this->dateFilter : null,
            'search' => $this->search,
        ], 10);

        $accessibleShelters = $healthCheckService->getAccessibleShelters($user);

        $filteredShelters = $this->shelterSearch !== ''
            ? $accessibleShelters->filter(fn ($s) => str_contains(strtolower($s->name), strtolower(trim($this->shelterSearch))))->values()
            : $accessibleShelters;

        $currentShelter = $selectedShelterId
            ? $accessibleShelters->firstWhere('id', $selectedShelterId)
            : ($accessibleShelters->count() === 1 ? $accessibleShelters->first() : null);

        $summary = $healthCheckService->getDailyTriageSummary(
            $user,
            $this->dateFilter !== '' ? $this->dateFilter : null,
            $selectedShelterId
        );

        $selectableRefugees = $healthCheckService->getSelectableRefugees(
            $user,
            $selectedShelterId,
            $this->refugeeSearch !== '' ? $this->refugeeSearch : null,
            15
        );

        $chosenRefugee = ! empty($this->refugee_id)
            ? Refugee::with('shelter')->find((int) $this->refugee_id)
            : null;

        if ($this->panelMode === 'history' && $this->historyRefugee !== null) {
            $this->historyRefugee = $healthCheckService->getRefugeeHistory($user, $this->historyRefugee->id);
        }

        return view('livewire.admin.health-check-management', [
            'checks' => $checks,
            'accessibleShelters' => $accessibleShelters,
            'filteredShelters' => $filteredShelters,
            'currentShelter' => $currentShelter,
            'summary' => $summary,
            'selectableRefugees' => $selectableRefugees,
            'chosenRefugee' => $chosenRefugee,
            'canManage' => $user->isNakes(),
            'authUser' => $user,
        ]);
    }
}

