<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\HealthCheck;
use App\Models\MedicalReferral;
use App\Models\Shelter;
use App\Models\User;
use App\Notifications\MedicalReferralCreatedNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MedicalReferralService
{
    /**
     * Ambil daftar posko yang datanya dapat diakses pengguna.
     * Nakes & admin: seluruh posko. Koordinator: posko kelolaannya saja.
     */
    public function getAccessibleShelters(User $user): Collection
    {
        if ($user->isAdmin() || $user->isNakes()) {
            return Shelter::orderBy('id')->get();
        }

        if ($user->isKoordinator()) {
            return $user->managedShelters()->orderBy('id')->get();
        }

        return new Collection;
    }

    /**
     * AC 1: Daftar pemeriksaan yang belum memiliki rujukan aktif (prioritas Triase Merah di atas).
     */
    public function getSelectableHealthChecks(User $user, ?int $shelterId = null, ?string $search = null, int $limit = 50): Collection
    {
        $this->ensureCanView($user);

        $query = HealthCheck::query()
            ->with(['refugee.shelter', 'nakes'])
            ->whereDoesntHave('referrals', function (Builder $q) {
                $q->notCancelled();
            });

        if ($shelterId !== null) {
            $this->ensureCanAccessShelter($user, $shelterId);
            $query->forShelters([$shelterId]);
        } elseif ($user->isKoordinator()) {
            $query->forShelters($this->managedShelterIds($user));
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function (Builder $q) use ($term) {
                $q->where('complaint', 'like', '%'.$term.'%')
                    ->orWhereHas('refugee', fn (Builder $rq) => $rq->search($term));
            });
        }

        return $query
            ->orderByRaw("CASE WHEN triage = 'merah' THEN 1 WHEN triage = 'kuning' THEN 2 ELSE 3 END")
            ->orderBy('checked_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * AC 2: Daftar rujukan medis per posko dengan filter status dan pencarian.
     *
     * @param  array{shelter_id?: int|string|null, status?: string|null, search?: string|null}  $filters
     */
    public function listReferrals(User $user, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->ensureCanView($user);

        $query = MedicalReferral::query()
            ->with(['healthCheck.refugee.shelter', 'healthCheck.nakes', 'creator']);

        $this->applyShelterScope($user, $query, $filters['shelter_id'] ?? null);

        if (! empty($filters['status']) && in_array($filters['status'], MedicalReferral::STATUSES, true)) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($term) {
                $q->where('facility_name', 'like', '%'.$term.'%')
                    ->orWhere('reason', 'like', '%'.$term.'%')
                    ->orWhere('patient_condition', 'like', '%'.$term.'%')
                    ->orWhereHas('healthCheck.refugee', fn (Builder $rq) => $rq->withTrashed()->search($term));
            });
        }

        return $query
            ->orderByRaw(
                "CASE status
                    WHEN 'diajukan' THEN 1
                    WHEN 'dalam_perjalanan' THEN 2
                    WHEN 'diterima_faskes' THEN 3
                    WHEN 'selesai' THEN 4
                    ELSE 5
                END"
            )
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Ringkasan jumlah rujukan per status dan triase merah yang belum dirujuk.
     *
     * @return array{diajukan: int, dalam_perjalanan: int, diterima_faskes: int, selesai: int, dibatalkan: int, active: int, total: int, unreferred_merah: int}
     */
    public function getSummary(User $user, ?int $shelterId = null): array
    {
        $this->ensureCanView($user);

        $query = MedicalReferral::query();
        $this->applyShelterScope($user, $query, $shelterId);

        $counts = $query
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $diajukan = (int) ($counts[MedicalReferral::STATUS_DIAJUKAN] ?? 0);
        $dalamPerjalanan = (int) ($counts[MedicalReferral::STATUS_DALAM_PERJALANAN] ?? 0);
        $diterimaFaskes = (int) ($counts[MedicalReferral::STATUS_DITERIMA_FASKES] ?? 0);
        $selesai = (int) ($counts[MedicalReferral::STATUS_SELESAI] ?? 0);
        $dibatalkan = (int) ($counts[MedicalReferral::STATUS_DIBATALKAN] ?? 0);

        $unreferredQuery = HealthCheck::query()
            ->where('triage', 'merah')
            ->whereDoesntHave('referrals', fn (Builder $q) => $q->notCancelled());

        if ($shelterId !== null) {
            $unreferredQuery->forShelters([$shelterId]);
        } elseif ($user->isKoordinator()) {
            $unreferredQuery->forShelters($this->managedShelterIds($user));
        }

        return [
            'diajukan' => $diajukan,
            'dalam_perjalanan' => $dalamPerjalanan,
            'diterima_faskes' => $diterimaFaskes,
            'selesai' => $selesai,
            'dibatalkan' => $dibatalkan,
            'active' => $diajukan + $dalamPerjalanan + $diterimaFaskes,
            'total' => $diajukan + $dalamPerjalanan + $diterimaFaskes + $selesai + $dibatalkan,
            'unreferred_merah' => $unreferredQuery->count(),
        ];
    }

    /**
     * AC 2: Detail rujukan lengkap dengan pemeriksaan asal dan riwayat status.
     */
    public function getReferralDetail(User $user, int $referralId): MedicalReferral
    {
        $this->ensureCanView($user);

        $referral = MedicalReferral::with([
            'healthCheck.refugee.shelter',
            'healthCheck.nakes',
            'creator',
            'activityLogs.user',
        ])->findOrFail($referralId);

        $this->ensureCanAccessShelter($user, (int) $referral->healthCheck?->refugee?->shelter_id);

        return $referral;
    }

    /**
     * AC 2: Format riwayat status & catatan perkembangan kronologis untuk ditampilkan di detail.
     *
     * @return list<array<string, mixed>>
     */
    public function getStatusHistory(MedicalReferral $referral): array
    {
        $referral->loadMissing(['activityLogs.user', 'creator']);

        $entries = [];

        foreach ($referral->activityLogs as $log) {
            $changes = is_array($log->changes) ? $log->changes : [];
            $status = $changes['new_status'] ?? $changes['status'] ?? null;
            $oldStatus = $changes['old_status'] ?? null;
            $outcome = $changes['outcome'] ?? null;

            $entries[] = [
                'id' => $log->id,
                'action' => $log->action,
                'status' => $status,
                'status_label' => $status ? MedicalReferral::statusLabel($status) : null,
                'old_status' => $oldStatus,
                'old_status_label' => $oldStatus ? MedicalReferral::statusLabel($oldStatus) : null,
                'note' => $changes['note'] ?? null,
                'outcome' => $outcome,
                'outcome_label' => MedicalReferral::outcomeLabel($outcome),
                'cancel_reason' => $changes['cancel_reason'] ?? null,
                'actor_name' => $log->user?->name ?? 'Sistem',
                'actor_role' => $log->user?->role_label ?? null,
                'created_at' => $log->created_at?->toIso8601String(),
                'created_at_formatted' => $log->created_at?->format('d/m/Y H.i'),
            ];
        }

        if (empty($entries)) {
            $entries[] = [
                'id' => 0,
                'action' => 'created_medical_referral',
                'status' => $referral->status,
                'status_label' => $referral->status_label,
                'old_status' => null,
                'old_status_label' => null,
                'note' => 'Rujukan medis tercatat pada sistem.',
                'outcome' => $referral->outcome,
                'outcome_label' => $referral->outcome_label,
                'cancel_reason' => $referral->cancel_reason,
                'actor_name' => $referral->creator?->name ?? 'Tenaga Kesehatan',
                'actor_role' => $referral->creator?->role_label ?? null,
                'created_at' => $referral->created_at?->toIso8601String(),
                'created_at_formatted' => $referral->created_at?->format('d/m/Y H.i'),
            ];
        }

        return $entries;
    }

    /**
     * AC 1: Buat rujukan medis baru dari pemeriksaan (status awal "Diajukan", notifikasi ke koordinator).
     *
     * @param  array<string, mixed>  $data
     */
    public function createReferral(User $user, array $data): MedicalReferral
    {
        $this->ensureIsNakes($user);

        $healthCheck = HealthCheck::with('refugee.shelter.coordinator')
            ->find((int) ($data['health_check_id'] ?? 0));

        if (! $healthCheck) {
            throw ValidationException::withMessages([
                'health_check_id' => ['Catatan pemeriksaan asal tidak ditemukan atau sudah dihapus.'],
            ]);
        }

        $hasActiveReferral = $healthCheck->referrals()
            ->notCancelled()
            ->exists();

        if ($hasActiveReferral) {
            throw ValidationException::withMessages([
                'health_check_id' => ['Pemeriksaan ini sudah memiliki rujukan medis yang aktif.'],
            ]);
        }

        $referral = DB::transaction(function () use ($user, $healthCheck, $data) {
            $referral = MedicalReferral::create([
                'health_check_id' => $healthCheck->id,
                'facility_name' => trim((string) $data['facility_name']),
                'reason' => trim((string) $data['reason']),
                'patient_condition' => trim((string) $data['patient_condition']),
                'transport_needed' => (bool) ($data['transport_needed'] ?? false),
                'status' => MedicalReferral::STATUS_DIAJUKAN,
                'outcome' => null,
                'cancel_reason' => null,
                'created_by' => $user->id,
            ]);

            ActivityLog::record($user->id, $referral, 'created_medical_referral', [
                'status' => MedicalReferral::STATUS_DIAJUKAN,
                'health_check_id' => $healthCheck->id,
                'shelter_id' => $healthCheck->refugee?->shelter_id,
                'facility_name' => $referral->facility_name,
                'transport_needed' => $referral->transport_needed,
                'note' => 'Rujukan medis diajukan oleh tenaga kesehatan.',
            ]);

            return $referral;
        });

        $this->notifyCoordinator($referral, $healthCheck);

        return $referral->fresh([
            'healthCheck.refugee.shelter',
            'healthCheck.nakes',
            'creator',
            'activityLogs.user',
        ]);
    }

    /**
     * AC 3: Nakes atau koordinator posko memperbarui status rujukan dan catatan perkembangan.
     * Status "Selesai" mewajibkan hasil akhir (kembali ke posko atau dirawat).
     *
     * @param  array<string, mixed>  $data
     */
    public function updateReferralStatus(User $user, MedicalReferral $referral, array $data): MedicalReferral
    {
        $referral->loadMissing('healthCheck.refugee.shelter');
        $this->ensureCanUpdate($user, $referral);

        if (! $referral->canUpdateStatus()) {
            throw ValidationException::withMessages([
                'status' => ['Rujukan yang sudah selesai atau dibatalkan tidak dapat diperbarui lagi.'],
            ]);
        }

        $newStatus = (string) ($data['status'] ?? '');

        if (! in_array($newStatus, MedicalReferral::UPDATABLE_STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => ['Status rujukan yang dipilih tidak valid.'],
            ]);
        }

        if (! $referral->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages([
                'status' => [
                    sprintf(
                        'Status tidak dapat diubah mundur dari %s ke %s.',
                        $referral->status_label,
                        MedicalReferral::statusLabel($newStatus)
                    ),
                ],
            ]);
        }

        $outcome = null;
        if ($newStatus === MedicalReferral::STATUS_SELESAI) {
            $rawOutcome = trim((string) ($data['outcome'] ?? ''));
            if (! in_array($rawOutcome, MedicalReferral::OUTCOMES, true)) {
                throw ValidationException::withMessages([
                    'outcome' => ['Hasil akhir (kembali ke posko atau dirawat) wajib dipilih saat status Selesai.'],
                ]);
            }
            $outcome = $rawOutcome;
        }

        $note = trim((string) ($data['progress_note'] ?? ''));
        $oldStatus = $referral->status;

        $updated = DB::transaction(function () use ($user, $referral, $data, $oldStatus, $newStatus, $outcome, $note) {
            if ($user->isNakes() && $referral->isDiajukan()) {
                if (array_key_exists('facility_name', $data) && trim((string) $data['facility_name']) !== '') {
                    $referral->facility_name = trim((string) $data['facility_name']);
                }
                if (array_key_exists('reason', $data) && trim((string) $data['reason']) !== '') {
                    $referral->reason = trim((string) $data['reason']);
                }
                if (array_key_exists('patient_condition', $data) && trim((string) $data['patient_condition']) !== '') {
                    $referral->patient_condition = trim((string) $data['patient_condition']);
                }
                if (array_key_exists('transport_needed', $data)) {
                    $referral->transport_needed = (bool) $data['transport_needed'];
                }
            }

            $referral->status = $newStatus;
            if ($outcome !== null) {
                $referral->outcome = $outcome;
            }
            $referral->save();

            ActivityLog::record($user->id, $referral, 'updated_medical_referral_status', [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note' => $note !== '' ? $note : null,
                'outcome' => $outcome,
            ]);

            return $referral;
        });

        return $updated->fresh([
            'healthCheck.refugee.shelter',
            'healthCheck.nakes',
            'creator',
            'activityLogs.user',
        ]);
    }

    /**
     * AC 4: Batalkan rujukan berstatus "Diajukan" dengan alasan wajib.
     * Rujukan yang sudah berjalan tidak dapat dibatalkan/dihapus.
     */
    public function cancelReferral(User $user, MedicalReferral $referral, string $cancelReason): MedicalReferral
    {
        $this->ensureIsNakes($user);

        $cleanReason = trim($cancelReason);
        if ($cleanReason === '') {
            throw ValidationException::withMessages([
                'cancel_reason' => ['Alasan pembatalan rujukan medis wajib diisi.'],
            ]);
        }

        if (! $referral->canBeCancelled()) {
            throw ValidationException::withMessages([
                'cancel' => ['Rujukan yang sudah berjalan, selesai, atau dibatalkan tidak dapat dibatalkan/dihapus.'],
            ]);
        }

        $oldStatus = $referral->status;

        $cancelled = DB::transaction(function () use ($user, $referral, $oldStatus, $cleanReason) {
            $referral->status = MedicalReferral::STATUS_DIBATALKAN;
            $referral->cancel_reason = $cleanReason;
            $referral->save();

            ActivityLog::record($user->id, $referral, 'cancelled_medical_referral', [
                'old_status' => $oldStatus,
                'new_status' => MedicalReferral::STATUS_DIBATALKAN,
                'cancel_reason' => $cleanReason,
                'note' => 'Rujukan dibatalkan: '.$cleanReason,
            ]);

            return $referral;
        });

        return $cancelled->fresh([
            'healthCheck.refugee.shelter',
            'healthCheck.nakes',
            'creator',
            'activityLogs.user',
        ]);
    }

    /**
     * Kirim notifikasi rujukan baru ke koordinator posko terkait.
     */
    protected function notifyCoordinator(MedicalReferral $referral, HealthCheck $healthCheck): void
    {
        $coordinator = $healthCheck->refugee?->shelter?->coordinator;

        if ($coordinator instanceof User) {
            $coordinator->notify(new MedicalReferralCreatedNotification($referral));
        }
    }

    protected function applyShelterScope(User $user, Builder $query, int|string|null $shelterId): void
    {
        if (! empty($shelterId)) {
            $this->ensureCanAccessShelter($user, (int) $shelterId);
            $query->forShelters([(int) $shelterId]);

            return;
        }

        if ($user->isKoordinator()) {
            $query->forShelters($this->managedShelterIds($user));
        }
    }

    /**
     * @return list<int>
     */
    protected function managedShelterIds(User $user): array
    {
        return $user->managedShelters()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    protected function ensureCanView(User $user): void
    {
        if (! $user->isNakes() && ! $user->isKoordinator() && ! $user->isAdmin()) {
            abort(403, 'Akses Ditolak: Data rujukan medis hanya dapat diakses Tenaga Kesehatan dan Koordinator Posko terkait.');
        }
    }

    protected function ensureCanAccessShelter(User $user, int $shelterId): void
    {
        if ($user->isKoordinator() && ! in_array($shelterId, $this->managedShelterIds($user), true)) {
            abort(403, 'Akses Ditolak: Data rujukan medis ini bukan berasal dari posko kelolaan Anda.');
        }
    }

    protected function ensureCanUpdate(User $user, MedicalReferral $referral): void
    {
        if ($user->isNakes()) {
            return;
        }

        if ($user->isKoordinator()) {
            $this->ensureCanAccessShelter($user, (int) $referral->healthCheck?->refugee?->shelter_id);

            return;
        }

        abort(403, 'Akses Ditolak: Hanya Tenaga Kesehatan atau Koordinator Posko terkait yang dapat memperbarui status rujukan.');
    }

    protected function ensureIsNakes(User $user): void
    {
        if (! $user->isNakes()) {
            abort(403, 'Akses Ditolak: Hanya Tenaga Kesehatan yang dapat membuat atau membatalkan rujukan medis.');
        }
    }
}
