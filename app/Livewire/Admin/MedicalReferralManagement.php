<?php

namespace App\Livewire\Admin;

use App\Http\Requests\MedicalReferral\CancelMedicalReferralRequest;
use App\Http\Requests\MedicalReferral\StoreMedicalReferralRequest;
use App\Http\Requests\MedicalReferral\UpdateMedicalReferralRequest;
use App\Models\HealthCheck;
use App\Models\MedicalReferral;
use App\Models\User;
use App\Services\MedicalReferralService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Rujukan Medis - ResQHub')]
class MedicalReferralManagement extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public const CANCEL_REASON_PRESETS = [
        'Kondisi membaik setelah observasi',
        'Salah memilih pasien atau pemeriksaan',
        'Pasien / keluarga menolak rujukan',
        'Ditangani di posko kesehatan',
    ];

    // Filters
    public string $shelterFilter = '';

    public string $statusFilter = '';

    public string $search = '';

    // Panel & Modal state
    public string $panelMode = 'create'; // 'create' | 'edit' | 'detail'

    public bool $showCancelModal = false;

    // Form fields
    public ?int $referralId = null;

    public string|int $health_check_id = '';

    public string $facility_name = '';

    public string $reason = '';

    public string $patient_condition = '';

    public bool $transport_needed = true;

    public string $status = MedicalReferral::STATUS_DIAJUKAN;

    public string $outcome = '';

    public string $progress_note = '';

    public string $cancel_reason = 'Kondisi membaik setelah observasi';

    public string $custom_cancel_reason = '';

    // Selected entities
    public ?MedicalReferral $selectedReferral = null;

    /** @var list<array<string, mixed>> */
    public array $statusHistory = [];

    public ?array $notification = null;

    public function mount(MedicalReferralService $referralService): void
    {
        $user = $this->currentUser();

        if (! $user->isNakes() && ! $user->isKoordinator() && ! $user->isAdmin()) {
            abort(403, 'Akses Ditolak: Data rujukan medis hanya dapat diakses Tenaga Kesehatan dan Koordinator Posko terkait.');
        }

        $shelters = $referralService->getAccessibleShelters($user);
        if ($user->isKoordinator() && $shelters->count() === 1) {
            $this->shelterFilter = (string) $shelters->first()->id;
        }

        if (! $user->isNakes()) {
            $this->panelMode = 'detail';
        }

        $referralParam = (int) request()->query('referral', 0);
        if ($referralParam > 0) {
            $this->openDetailPanel($referralParam, $referralService);

            return;
        }

        $healthCheckParam = (int) request()->query('health_check', 0);
        if ($healthCheckParam > 0 && $user->isNakes()) {
            $this->openCreatePanel($healthCheckParam);
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

    public function selectShelter(?int $shelterId = null): void
    {
        $this->shelterFilter = $shelterId === null ? '' : (string) $shelterId;
        $this->resetPage();
    }

    public function selectHealthCheck(int $healthCheckId): void
    {
        $check = HealthCheck::with('refugee.shelter')->find($healthCheckId);
        if ($check) {
            $this->health_check_id = $check->id;
            $this->resetErrorBag('health_check_id');
        }
    }

    public function clearSelectedHealthCheck(): void
    {
        $this->health_check_id = '';
    }

    public function openCreatePanel(?int $healthCheckId = null): void
    {
        $this->ensureNakes();
        $this->resetForm();
        if ($healthCheckId !== null) {
            $this->selectHealthCheck($healthCheckId);
        }
        $this->panelMode = 'create';
    }

    public function closeFormPanel(): void
    {
        $this->resetForm();
        $this->panelMode = $this->currentUser()->isNakes() ? 'create' : 'detail';
    }

    public function storeReferral(MedicalReferralService $referralService): void
    {
        $this->ensureNakes();

        $validated = $this->validate(
            StoreMedicalReferralRequest::referralRules(),
            StoreMedicalReferralRequest::referralMessages()
        );

        $referral = $referralService->createReferral($this->currentUser(), $validated);

        $this->resetForm();
        $this->openDetailPanel($referral->id, $referralService);
        $this->notify(
            "Rujukan medis {$referral->healthCheck?->refugee?->head_name} ke {$referral->facility_name} diajukan. Koordinator posko telah dinotifikasi."
        );
    }

    public function openEditPanel(int $id, MedicalReferralService $referralService): void
    {
        $referral = $referralService->getReferralDetail($this->currentUser(), $id);
        $user = $this->currentUser();

        if ((! $user->isNakes() && ! $user->isKoordinator()) || ! $referral->canUpdateStatus()) {
            $this->openDetailPanel($id, $referralService);

            return;
        }

        $this->selectedReferral = $referral;
        $this->statusHistory = $referralService->getStatusHistory($referral);
        $this->referralId = $referral->id;
        $this->health_check_id = $referral->health_check_id;
        $this->facility_name = $referral->facility_name;
        $this->reason = $referral->reason;
        $this->patient_condition = $referral->patient_condition;
        $this->transport_needed = (bool) $referral->transport_needed;
        $this->status = $referral->status;
        $this->outcome = (string) ($referral->outcome ?? '');
        $this->progress_note = '';
        $this->resetErrorBag();
        $this->panelMode = 'edit';
    }

    public function updateReferral(MedicalReferralService $referralService): void
    {
        $user = $this->currentUser();
        if (! $user->isNakes() && ! $user->isKoordinator()) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk memperbarui status rujukan.');
        }

        $rules = [
            'status' => UpdateMedicalReferralRequest::updateRules()['status'],
            'progress_note' => UpdateMedicalReferralRequest::updateRules()['progress_note'],
            'outcome' => UpdateMedicalReferralRequest::updateRules()['outcome'],
        ];

        $referral = MedicalReferral::with('healthCheck.refugee')->findOrFail($this->referralId);

        if ($user->isNakes() && $referral->isDiajukan()) {
            $rules['facility_name'] = ['required', 'string', 'max:150'];
            $rules['reason'] = ['required', 'string', 'max:1000'];
            $rules['patient_condition'] = ['required', 'string', 'max:1000'];
            $rules['transport_needed'] = ['required', 'boolean'];
        }

        $validated = $this->validate(
            $rules,
            array_merge(
                StoreMedicalReferralRequest::referralMessages(),
                UpdateMedicalReferralRequest::updateMessages()
            )
        );

        $updated = $referralService->updateReferralStatus($user, $referral, $validated);

        $this->openDetailPanel($updated->id, $referralService);
        $this->notify(
            "Status rujukan {$updated->healthCheck?->refugee?->head_name} berhasil diperbarui menjadi {$updated->status_label}."
        );
    }

    public function openDetailPanel(int $id, ?MedicalReferralService $referralService = null): void
    {
        $referralService ??= app(MedicalReferralService::class);

        $referral = $referralService->getReferralDetail($this->currentUser(), $id);
        $this->selectedReferral = $referral;
        $this->referralId = $referral->id;
        $this->statusHistory = $referralService->getStatusHistory($referral);
        $this->resetErrorBag();
        $this->panelMode = 'detail';
    }

    public function openCancelModal(int $id, MedicalReferralService $referralService): void
    {
        $this->ensureNakes();
        $this->selectedReferral = $referralService->getReferralDetail($this->currentUser(), $id);
        $this->referralId = $this->selectedReferral->id;
        $this->cancel_reason = self::CANCEL_REASON_PRESETS[0];
        $this->custom_cancel_reason = '';
        $this->resetErrorBag();
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->cancel_reason = self::CANCEL_REASON_PRESETS[0];
        $this->custom_cancel_reason = '';
        $this->resetErrorBag();
    }

    public function confirmCancel(MedicalReferralService $referralService): void
    {
        $this->ensureNakes();

        $effectiveReason = $this->cancel_reason === 'lainnya'
            ? trim($this->custom_cancel_reason)
            : trim($this->cancel_reason);

        $this->cancel_reason = $effectiveReason;

        $this->validate(
            CancelMedicalReferralRequest::cancelRules(),
            CancelMedicalReferralRequest::cancelMessages()
        );

        $referral = MedicalReferral::findOrFail($this->referralId);
        $cancelled = $referralService->cancelReferral($this->currentUser(), $referral, $effectiveReason);

        $this->closeCancelModal();
        $this->openDetailPanel($cancelled->id, $referralService);
        $this->notify("Rujukan medis {$cancelled->healthCheck?->refugee?->head_name} berhasil dibatalkan.");
    }

    public function dismissNotification(): void
    {
        $this->notification = null;
    }

    protected function resetForm(): void
    {
        $this->referralId = null;
        $this->selectedReferral = null;
        $this->statusHistory = [];
        $this->health_check_id = '';
        $this->facility_name = '';
        $this->reason = '';
        $this->patient_condition = '';
        $this->transport_needed = true;
        $this->status = MedicalReferral::STATUS_DIAJUKAN;
        $this->outcome = '';
        $this->progress_note = '';
        $this->cancel_reason = self::CANCEL_REASON_PRESETS[0];
        $this->custom_cancel_reason = '';
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
            abort(403, 'Akses Ditolak: Hanya Tenaga Kesehatan yang dapat membuat atau membatalkan rujukan medis.');
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

    public function render(MedicalReferralService $referralService)
    {
        $user = $this->currentUser();
        $selectedShelterId = $this->shelterFilter !== '' ? (int) $this->shelterFilter : null;

        $referrals = $referralService->listReferrals($user, [
            'shelter_id' => $selectedShelterId,
            'status' => $this->statusFilter,
            'search' => $this->search,
        ], 10);

        $accessibleShelters = $referralService->getAccessibleShelters($user);
        $summary = $referralService->getSummary($user, $selectedShelterId);

        $selectableChecks = $user->isNakes()
            ? $referralService->getSelectableHealthChecks($user, $selectedShelterId, null, 100)
            : collect();

        if ($this->selectedReferral !== null && $this->referralId !== null) {
            $fresh = MedicalReferral::with([
                'healthCheck.refugee.shelter',
                'healthCheck.nakes',
                'creator',
                'activityLogs.user',
            ])->find($this->referralId);

            if ($fresh) {
                $this->selectedReferral = $fresh;
                $this->statusHistory = $referralService->getStatusHistory($fresh);
            }
        }

        return view('livewire.admin.medical-referral-management', [
            'referrals' => $referrals,
            'accessibleShelters' => $accessibleShelters,
            'summary' => $summary,
            'selectableChecks' => $selectableChecks,
            'canCreate' => $user->isNakes(),
            'canUpdate' => $user->isNakes() || $user->isKoordinator(),
            'canCancel' => $user->isNakes(),
            'authUser' => $user,
        ]);
    }
}
