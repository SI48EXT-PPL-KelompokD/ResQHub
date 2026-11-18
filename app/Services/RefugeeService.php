<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Refugee;
use App\Models\RefugeeMovement;
use App\Models\Shelter;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefugeeService
{
    /**
     * Ambil daftar posko yang dapat diakses oleh pengguna.
     */
    public function getAccessibleShelters(User $user): Collection
    {
        if ($user->isAdmin() || $user->isNakes()) {
            return Shelter::orderBy('id')->get();
        }

        if ($user->isKoordinator()) {
            return $user->managedShelters()->orderBy('id')->get();
        }

        return new Collection();
    }

    /**
     * Ambil daftar posko tujuan untuk perpindahan (aktif/penuh namun tidak ditutup, selain posko asal).
     */
    public function getDestinationShelters(?int $excludeShelterId = null): Collection
    {
        return Shelter::query()
            ->where('status', '!=', 'ditutup')
            ->when($excludeShelterId, fn ($q) => $q->where('id', '!=', $excludeShelterId))
            ->orderBy('name')
            ->get();
    }

    /**
     * AC 2: Ambil daftar pengungsi per posko dengan pencarian dan filter status.
     *
     * @param array{shelter_id?: int|string|null, search?: string|null, status?: string|null} $filters
     */
    public function listRefugees(User $user, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->ensureCanViewModule($user);
        $this->reconcileTransferredRefugees();

        $query = Refugee::query()
            ->with(['shelter'])
            ->withCount(['movements', 'healthChecks', 'distributions']);

        $shelterId = !empty($filters['shelter_id']) ? (int) $filters['shelter_id'] : null;

        if ($shelterId !== null) {
            if ($user->isKoordinator() && !$user->canManageShelter($shelterId)) {
                abort(403, 'Akses Ditolak: Anda hanya dapat mengakses data pengungsi pada posko kelolaan Anda.');
            }

            $query->forShelter($shelterId);
        } elseif ($user->isKoordinator()) {
            $managedIds = $user->managedShelters()->pluck('id')->all();
            $query->forShelters($managedIds);
        }

        $query->search($filters['search'] ?? null)
            ->byStatus($filters['status'] ?? null, $shelterId);

        $sort = $filters['sort'] ?? 'latest';
        match ($sort) {
            'oldest' => $query->orderBy('checked_in_at', 'asc')->orderBy('id', 'asc'),
            'members_desc' => $query->orderBy('members', 'desc')->orderBy('checked_in_at', 'desc'),
            'name_asc' => $query->orderBy('head_name', 'asc'),
            default => $query->orderBy('checked_in_at', 'desc')->orderBy('id', 'desc'),
        };

        return $query->paginate($perPage);
    }

    /**
     * AC 2: Ambil detail pengungsi beserta riwayat masuk, pindah, dan pulang.
     */
    public function getRefugeeDetail(User $user, int $refugeeId): Refugee
    {
        $this->ensureCanViewModule($user);
        $this->reconcileTransferredRefugees();

        $refugee = Refugee::with([
            'shelter',
            'movements.fromShelter',
            'movements.toShelter',
            'movements.creator',
            'healthChecks',
            'distributions',
        ])->findOrFail($refugeeId);

        if ($user->isKoordinator()) {
            $managedIds = $user->managedShelters()->pluck('id')->map(fn ($id) => (int) $id)->all();
            $inManagedShelter = in_array((int) $refugee->shelter_id, $managedIds, true);
            $hasMovementInManagedShelter = $refugee->movements->contains(function (RefugeeMovement $m) use ($managedIds) {
                return in_array((int) $m->from_shelter_id, $managedIds, true)
                    || in_array((int) $m->to_shelter_id, $managedIds, true);
            });

            if (!$inManagedShelter && !$hasMovementInManagedShelter) {
                abort(403, 'Akses Ditolak: Pengungsi ini tidak berada di posko kelolaan Anda.');
            }
        }

        return $refugee;
    }

    /**
     * Evaluasi peringatan saat check-in atau perubahan data:
     * 1. Bila penambahan anggota melebihi kapasitas posko.
     * 2. Bila NIK masih tercatat aktif di posko lain (atau posko saat ini).
     *
     * @return list<array{type: string, message: string}>
     */
    public function evaluateWarnings(
        Shelter $shelter,
        int $additionalMembers,
        ?string $nik = null,
        ?int $ignoreRefugeeId = null
    ): array {
        $warnings = [];

        // 1. Cek peringatan kapasitas posko
        if ($additionalMembers > 0 && $shelter->wouldExceedCapacity($additionalMembers)) {
            $projectedOccupancy = $shelter->occupancy + $additionalMembers;
            $warnings[] = [
                'type' => 'capacity_exceeded',
                'message' => "Peringatan: Okupansi {$shelter->name} ({$projectedOccupancy}/{$shelter->capacity} jiwa) melebihi kapasitas maksimal posko!",
            ];
        }

        // 2. Cek peringatan bila NIK masih aktif di posko lain / posko aktif
        $cleanNik = !empty($nik) ? trim($nik) : null;
        if ($cleanNik !== null) {
            $activeDuplicate = Refugee::with('shelter')
                ->where('nik', $cleanNik)
                ->whereIn('status', ['di_posko', 'pindah'])
                ->when($ignoreRefugeeId, fn ($q) => $q->where('id', '!=', $ignoreRefugeeId))
                ->first();

            if ($activeDuplicate !== null) {
                $shelterName = $activeDuplicate->shelter?->name ?? 'posko lain';
                $warnings[] = [
                    'type' => 'duplicate_active_nik',
                    'message' => "Peringatan: NIK {$cleanNik} masih tercatat aktif di {$shelterName} (Kepala Keluarga: {$activeDuplicate->head_name}).",
                ];
            }
        }

        return $warnings;
    }

    /**
     * AC 1: Tambah (Check-In) pengungsi baru per kepala keluarga.
     * Okupansi posko bertambah otomatis & riwayat masuk tercatat.
     *
     * @param array<string, mixed> $data
     * @return array{refugee: Refugee, warnings: list<array{type: string, message: string}>}
     */
    public function checkIn(User $user, array $data): array
    {
        $shelter = Shelter::findOrFail($data['shelter_id']);
        $this->ensureCanManageShelter($user, $shelter);

        if ($shelter->status === 'ditutup') {
            throw ValidationException::withMessages([
                'shelter_id' => ['Posko yang dipilih sudah ditutup dan tidak dapat menerima check-in pengungsi baru.'],
            ]);
        }

        $members = (int) $data['members'];
        $infants = (int) ($data['infants'] ?? 0);
        $elderly = (int) ($data['elderly'] ?? 0);
        $pregnant = (int) ($data['pregnant'] ?? 0);
        $disabled = (int) ($data['disabled'] ?? 0);
        $nik = !empty($data['nik']) ? trim((string) $data['nik']) : null;

        $this->validateVulnerableCounts($members, $infants, $elderly, $pregnant, $disabled);

        return DB::transaction(function () use (
            $user,
            $shelter,
            $data,
            $members,
            $infants,
            $elderly,
            $pregnant,
            $disabled,
            $nik
        ) {
            /** @var Shelter $lockedShelter */
            $lockedShelter = Shelter::lockForUpdate()->findOrFail($shelter->id);

            $warnings = $this->evaluateWarnings($lockedShelter, $members, $nik);

            $refugee = Refugee::create([
                'shelter_id' => $lockedShelter->id,
                'head_name' => trim((string) $data['head_name']),
                'nik' => $nik,
                'members' => $members,
                'infants' => $infants,
                'elderly' => $elderly,
                'pregnant' => $pregnant,
                'disabled' => $disabled,
                'status' => 'di_posko',
                'checked_in_at' => $data['checked_in_at'] ?? now(),
                'checked_out_at' => null,
            ]);

            // Okupansi posko bertambah otomatis
            $lockedShelter->adjustOccupancy($members);

            // Catat riwayat check-in (masuk)
            RefugeeMovement::create([
                'refugee_id' => $refugee->id,
                'from_shelter_id' => null,
                'to_shelter_id' => $lockedShelter->id,
                'type' => 'masuk',
                'created_by' => $user->id,
            ]);

            ActivityLog::record($user->id, $refugee, 'checked_in_refugee', [
                'shelter_id' => $lockedShelter->id,
                'head_name' => $refugee->head_name,
                'members' => $members,
                'warnings' => array_column($warnings, 'type'),
            ]);

            return [
                'refugee' => $refugee->fresh(['shelter', 'movements.fromShelter', 'movements.toShelter', 'movements.creator']),
                'warnings' => $warnings,
            ];
        });
    }

    /**
     * AC 3 (Bagian 1): Perbarui data keluarga pengungsi.
     * Bila jumlah anggota keluarga berubah saat masih di posko, okupansi posko disesuaikan otomatis.
     *
     * @param array<string, mixed> $data
     * @return array{refugee: Refugee, warnings: list<array{type: string, message: string}>}
     */
    public function updateFamilyData(User $user, Refugee $refugee, array $data): array
    {
        $shelter = Shelter::findOrFail($refugee->shelter_id);
        $this->ensureCanManageShelter($user, $shelter);

        $newMembers = (int) $data['members'];
        $infants = (int) ($data['infants'] ?? 0);
        $elderly = (int) ($data['elderly'] ?? 0);
        $pregnant = (int) ($data['pregnant'] ?? 0);
        $disabled = (int) ($data['disabled'] ?? 0);
        $nik = !empty($data['nik']) ? trim((string) $data['nik']) : null;

        $this->validateVulnerableCounts($newMembers, $infants, $elderly, $pregnant, $disabled);

        return DB::transaction(function () use (
            $user,
            $refugee,
            $shelter,
            $data,
            $newMembers,
            $infants,
            $elderly,
            $pregnant,
            $disabled,
            $nik
        ) {
            /** @var Shelter $lockedShelter */
            $lockedShelter = Shelter::lockForUpdate()->findOrFail($shelter->id);

            $oldMembers = (int) $refugee->members;
            $delta = $newMembers - $oldMembers;

            $warnings = $this->evaluateWarnings(
                $lockedShelter,
                $refugee->isOccupyingShelter() ? max(0, $delta) : 0,
                $nik,
                $refugee->id
            );

            $refugee->update([
                'head_name' => trim((string) $data['head_name']),
                'nik' => $nik,
                'members' => $newMembers,
                'infants' => $infants,
                'elderly' => $elderly,
                'pregnant' => $pregnant,
                'disabled' => $disabled,
            ]);

            if ($delta !== 0 && $refugee->isOccupyingShelter()) {
                $lockedShelter->adjustOccupancy($delta);
            }

            ActivityLog::record($user->id, $refugee, 'updated_refugee_family', [
                'old_members' => $oldMembers,
                'new_members' => $newMembers,
                'occupancy_delta' => $refugee->isOccupyingShelter() ? $delta : 0,
            ]);

            return [
                'refugee' => $refugee->fresh(['shelter', 'movements.fromShelter', 'movements.toShelter', 'movements.creator']),
                'warnings' => $warnings,
            ];
        });
    }

    /**
     * AC 3 (Bagian 2): Aksi Kepulangan (Pulang).
     * Okupansi posko asal berkurang otomatis dan riwayat kepulangan tercatat.
     */
    public function checkOut(User $user, Refugee $refugee, ?string $checkedOutAt = null): Refugee
    {
        $fromShelter = Shelter::findOrFail($refugee->shelter_id);
        $this->ensureCanManageShelter($user, $fromShelter);

        if ($refugee->status === 'pulang') {
            throw ValidationException::withMessages([
                'status' => ['Pengungsi ini sudah berstatus pulang sebelumnya.'],
            ]);
        }

        return DB::transaction(function () use ($user, $refugee, $fromShelter, $checkedOutAt) {
            /** @var Shelter $lockedFromShelter */
            $lockedFromShelter = Shelter::lockForUpdate()->findOrFail($fromShelter->id);

            $refugee->update([
                'status' => 'pulang',
                'checked_out_at' => $checkedOutAt ?? now(),
            ]);

            // Kurangi okupansi posko asal secara otomatis
            $lockedFromShelter->adjustOccupancy(-((int) $refugee->members));

            // Catat riwayat kepulangan
            RefugeeMovement::create([
                'refugee_id' => $refugee->id,
                'from_shelter_id' => $lockedFromShelter->id,
                'to_shelter_id' => null,
                'type' => 'pulang',
                'created_by' => $user->id,
            ]);

            ActivityLog::record($user->id, $refugee, 'checked_out_refugee', [
                'from_shelter_id' => $lockedFromShelter->id,
                'members' => $refugee->members,
            ]);

            return $refugee->fresh(['shelter', 'movements.fromShelter', 'movements.toShelter', 'movements.creator']);
        });
    }

    /**
     * AC 3 (Bagian 3): Aksi Pindah Posko.
     * Memastikan posko tujuan memiliki kapasitas yang cukup, memperbarui okupansi posko asal & tujuan otomatis,
     * serta mencatat riwayat perpindahan.
     */
    public function transferShelter(User $user, Refugee $refugee, int $toShelterId): Refugee
    {
        $fromShelter = Shelter::findOrFail($refugee->shelter_id);
        $this->ensureCanManageShelter($user, $fromShelter);

        if ($refugee->status === 'pulang') {
            throw ValidationException::withMessages([
                'status' => ['Pengungsi yang sudah pulang tidak dapat dipindahkan ke posko lain.'],
            ]);
        }

        if ((int) $fromShelter->id === $toShelterId) {
            throw ValidationException::withMessages([
                'to_shelter_id' => ['Posko tujuan harus berbeda dengan posko asal saat ini.'],
            ]);
        }

        return DB::transaction(function () use ($user, $refugee, $fromShelter, $toShelterId) {
            /** @var Shelter $lockedFromShelter */
            $lockedFromShelter = Shelter::lockForUpdate()->findOrFail($fromShelter->id);
            /** @var Shelter $lockedToShelter */
            $lockedToShelter = Shelter::lockForUpdate()->findOrFail($toShelterId);

            if ($lockedToShelter->status === 'ditutup') {
                throw ValidationException::withMessages([
                    'to_shelter_id' => ["Posko tujuan ({$lockedToShelter->name}) berstatus ditutup."],
                ]);
            }

            $members = (int) $refugee->members;

            if (!$lockedToShelter->hasCapacityFor($members)) {
                $available = $lockedToShelter->availableCapacity();
                throw ValidationException::withMessages([
                    'to_shelter_id' => [
                        "Posko tujuan ({$lockedToShelter->name}) tidak memiliki kapasitas yang cukup (tersedia: {$available} jiwa, dibutuhkan: {$members} jiwa).",
                    ],
                ]);
            }

            // Perbarui data pengungsi
            $refugee->update([
                'shelter_id' => $lockedToShelter->id,
                'status' => 'pindah',
                'checked_out_at' => now(),
            ]);

            // Okupansi posko asal berkurang & posko tujuan bertambah otomatis
            $lockedFromShelter->adjustOccupancy(-$members);
            $lockedToShelter->adjustOccupancy($members);

            // Catat riwayat perpindahan posko
            RefugeeMovement::create([
                'refugee_id' => $refugee->id,
                'from_shelter_id' => $lockedFromShelter->id,
                'to_shelter_id' => $lockedToShelter->id,
                'type' => 'pindah',
                'created_by' => $user->id,
            ]);

            ActivityLog::record($user->id, $refugee, 'transferred_refugee', [
                'from_shelter_id' => $lockedFromShelter->id,
                'to_shelter_id' => $lockedToShelter->id,
                'members' => $members,
            ]);

            return $refugee->fresh(['shelter', 'movements.fromShelter', 'movements.toShelter', 'movements.creator']);
        });
    }

    /**
     * AC 4: Hapus (Soft Delete) data pengungsi yang salah input dengan alasan.
     * Hanya diizinkan bila belum memiliki catatan penyaluran bantuan atau pemeriksaan kesehatan;
     * okupansi posko dikoreksi otomatis.
     */
    public function deleteRefugee(User $user, Refugee $refugee, string $deleteReason): Refugee
    {
        $shelter = Shelter::findOrFail($refugee->shelter_id);
        $this->ensureCanManageShelter($user, $shelter);

        $cleanReason = trim($deleteReason);
        if ($cleanReason === '') {
            throw ValidationException::withMessages([
                'delete_reason' => ['Alasan penghapusan data pengungsi wajib diisi.'],
            ]);
        }

        if (!$refugee->canBeDeleted()) {
            $reasons = implode(' dan ', $refugee->deletionBlockReasons());
            throw ValidationException::withMessages([
                'delete' => [
                    "Data pengungsi tidak dapat dihapus karena sudah memiliki catatan {$reasons}.",
                ],
            ]);
        }

        return DB::transaction(function () use ($user, $refugee, $shelter, $cleanReason) {
            /** @var Shelter $lockedShelter */
            $lockedShelter = Shelter::lockForUpdate()->findOrFail($shelter->id);

            $wasOccupying = $refugee->isOccupyingShelter();
            $members = (int) $refugee->members;

            $refugee->delete_reason = $cleanReason;
            $refugee->save();
            $refugee->delete();

            if ($wasOccupying) {
                $lockedShelter->adjustOccupancy(-$members);
            }

            ActivityLog::record($user->id, $refugee, 'deleted_refugee', [
                'shelter_id' => $lockedShelter->id,
                'head_name' => $refugee->head_name,
                'members' => $members,
                'delete_reason' => $cleanReason,
                'occupancy_corrected' => $wasOccupying,
            ]);

            return $refugee;
        });
    }

    /**
     * Ambil ringkasan statistik pengungsi & kelompok rentan per posko atau seluruh posko kelolaan.
     *
     * @return array<string, int|float>
     */
    public function getSummaryStatistics(User $user, ?int $shelterId = null): array
    {
        $this->ensureCanViewModule($user);
        $this->reconcileTransferredRefugees();

        $shelterQuery = Shelter::query()->where('status', '!=', 'ditutup');
        $refugeeQuery = Refugee::query();

        if ($shelterId !== null) {
            $shelterQuery->where('id', $shelterId);
            $refugeeQuery->forShelter($shelterId);
        } elseif ($user->isKoordinator()) {
            $managedIds = $user->managedShelters()->pluck('id')->all();
            $shelterQuery->whereIn('id', $managedIds);
            $refugeeQuery->forShelters($managedIds);
        }

        $totalCapacity = (int) $shelterQuery->sum('capacity');
        $totalOccupancy = (int) $shelterQuery->sum('occupancy');
        $occupancyPercent = $totalCapacity > 0 ? round(($totalOccupancy / $totalCapacity) * 100, 1) : 0;

        $activeRefugeesQuery = Refugee::query()->whereIn('status', ['di_posko', 'pindah']);
        if ($shelterId !== null) {
            $activeRefugeesQuery->where('shelter_id', $shelterId);
        } elseif ($user->isKoordinator()) {
            $managedIds = $user->managedShelters()->pluck('id')->all();
            $activeRefugeesQuery->whereIn('shelter_id', $managedIds);
        }

        $aggregates = $activeRefugeesQuery->selectRaw('
            COUNT(*) as active_families,
            COALESCE(SUM(members), 0) as active_members,
            COALESCE(SUM(infants), 0) as total_infants,
            COALESCE(SUM(elderly), 0) as total_elderly,
            COALESCE(SUM(pregnant), 0) as total_pregnant,
            COALESCE(SUM(disabled), 0) as total_disabled
        ')->first();

        $totalVulnerable = (int) ($aggregates->total_infants ?? 0)
            + (int) ($aggregates->total_elderly ?? 0)
            + (int) ($aggregates->total_pregnant ?? 0)
            + (int) ($aggregates->total_disabled ?? 0);

        return [
            'active_families' => (int) ($aggregates->active_families ?? 0),
            'active_members' => (int) ($aggregates->active_members ?? 0),
            'total_infants' => (int) ($aggregates->total_infants ?? 0),
            'total_elderly' => (int) ($aggregates->total_elderly ?? 0),
            'total_pregnant' => (int) ($aggregates->total_pregnant ?? 0),
            'total_disabled' => (int) ($aggregates->total_disabled ?? 0),
            'total_vulnerable' => $totalVulnerable,
            'total_capacity' => $totalCapacity,
            'total_occupancy' => $totalOccupancy,
            'occupancy_percent' => $occupancyPercent,
            'count_di_posko' => (clone $refugeeQuery)->byStatus('di_posko', $shelterId)->count(),
            'count_pindah' => (clone $refugeeQuery)->byStatus('pindah', $shelterId)->count(),
            'count_pulang' => (clone $refugeeQuery)->byStatus('pulang', $shelterId)->count(),
        ];
    }

    /**
     * Sinkronisasi otomatis pengungsi berstatus 'pindah' agar shelter_id selalu
     * merujuk pada posko tujuan terakhir (to_shelter_id), bukan tertinggal di posko lama.
     */
    public function reconcileTransferredRefugees(): void
    {
        $transferredRefugees = Refugee::query()
            ->where('status', 'pindah')
            ->with(['movements' => fn ($q) => $q->where('type', 'pindah')])
            ->get();

        foreach ($transferredRefugees as $refugee) {
            /** @var RefugeeMovement|null $latestTransfer */
            $latestTransfer = $refugee->movements->first();

            if (
                $latestTransfer !== null
                && $latestTransfer->to_shelter_id !== null
                && (int) $refugee->shelter_id !== (int) $latestTransfer->to_shelter_id
            ) {
                $refugee->update([
                    'shelter_id' => (int) $latestTransfer->to_shelter_id,
                ]);
            }
        }
    }

    /**
     * Validasi jumlah kelompok rentan terhadap total anggota keluarga.
     */
    protected function validateVulnerableCounts(
        int $members,
        int $infants,
        int $elderly,
        int $pregnant,
        int $disabled
    ): void {
        if ($members < 1) {
            throw ValidationException::withMessages([
                'members' => ['Jumlah anggota keluarga minimal 1 orang.'],
            ]);
        }

        if ($infants < 0 || $elderly < 0 || $pregnant < 0 || $disabled < 0) {
            throw ValidationException::withMessages([
                'vulnerable' => ['Jumlah kelompok rentan tidak boleh bernilai negatif.'],
            ]);
        }

        $totalVulnerable = $infants + $elderly + $pregnant + $disabled;
        if ($totalVulnerable > $members) {
            throw ValidationException::withMessages([
                'members' => [
                    "Total anggota kelompok rentan ({$totalVulnerable} jiwa) tidak boleh melebihi jumlah seluruh anggota keluarga ({$members} jiwa).",
                ],
            ]);
        }
    }

    protected function ensureCanViewModule(User $user): void
    {
        if (!$user->canAccessModule('modul_c')) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki hak akses ke Modul Pengungsi & Kesehatan.');
        }
    }

    protected function ensureCanManageShelter(User $user, Shelter $shelter): void
    {
        if (!$user->canManageShelter($shelter)) {
            abort(403, 'Akses Ditolak: Hanya Koordinator Posko terkait yang dapat mengelola data pengungsi di posko ini.');
        }
    }
}
