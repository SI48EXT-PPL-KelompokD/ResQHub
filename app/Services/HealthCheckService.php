<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\HealthCheck;
use App\Models\Refugee;
use App\Models\Shelter;
use App\Models\User;
use App\Notifications\CriticalTriageNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HealthCheckService
{
    /**
     * Kolom yang dapat dikoreksi dan dicatat perubahannya pada log aktivitas.
     *
     * @var list<string>
     */
    protected const EDITABLE_FIELDS = [
        'complaint',
        'temperature',
        'blood_pressure',
        'pulse',
        'triage',
        'action',
        'checked_at',
    ];

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
     * AC 1: Daftar pengungsi terdaftar (aktif di posko) yang dapat dipilih untuk diperiksa.
     */
    public function getSelectableRefugees(User $user, ?int $shelterId = null, ?string $search = null, int $limit = 50): Collection
    {
        $this->ensureCanView($user);

        return Refugee::query()
            ->with('shelter')
            ->whereIn('status', ['di_posko', 'pindah'])
            ->when($shelterId, fn (Builder $q) => $q->where('shelter_id', $shelterId))
            ->when(
                $user->isKoordinator(),
                fn (Builder $q) => $q->whereIn('shelter_id', $this->managedShelterIds($user))
            )
            ->search($search)
            ->orderBy('head_name')
            ->limit($limit)
            ->get();
    }

    /**
     * AC 2: Daftar catatan pemeriksaan dengan filter posko, triase, tanggal, dan pencarian.
     *
     * @param  array{shelter_id?: int|string|null, triage?: string|null, date?: string|null, search?: string|null}  $filters
     */
    public function listHealthChecks(User $user, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->ensureCanView($user);

        $query = HealthCheck::query()
            ->with(['refugee.shelter', 'nakes'])
            ->withCount('referrals');

        $this->applyShelterScope($user, $query, $filters['shelter_id'] ?? null);

        if (! empty($filters['triage']) && in_array($filters['triage'], HealthCheck::TRIAGE_LEVELS, true)) {
            $query->where('triage', $filters['triage']);
        }

        if (! empty($filters['date'])) {
            $query->whereDate('checked_at', Carbon::parse($filters['date'])->toDateString());
        }

        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($term) {
                $q->where('complaint', 'like', '%'.$term.'%')
                    ->orWhereHas('refugee', fn (Builder $rq) => $rq->search($term));
            });
        }

        return $query
            ->orderByRaw("CASE WHEN triage = 'merah' THEN 1 WHEN triage = 'kuning' THEN 2 ELSE 3 END")
            ->orderBy('checked_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * AC 2: Riwayat pemeriksaan per pengungsi (terbaru di atas).
     */
    public function getRefugeeHistory(User $user, int $refugeeId): Refugee
    {
        $this->ensureCanView($user);

        $refugee = Refugee::withTrashed()->with('shelter')->findOrFail($refugeeId);
        $this->ensureCanAccessShelter($user, (int) $refugee->shelter_id);

        $refugee->setRelation(
            'healthChecks',
            HealthCheck::query()
                ->with(['nakes', 'referrals'])
                ->where('refugee_id', $refugee->id)
                ->orderBy('checked_at', 'desc')
                ->orderBy('id', 'desc')
                ->get()
        );

        return $refugee;
    }

    /**
     * Detail satu catatan pemeriksaan.
     */
    public function getHealthCheckDetail(User $user, int $healthCheckId): HealthCheck
    {
        $this->ensureCanView($user);

        $check = HealthCheck::with(['refugee.shelter', 'nakes', 'referrals'])->findOrFail($healthCheckId);
        $this->ensureCanAccessShelter($user, (int) $check->refugee?->shelter_id);

        return $check;
    }

    /**
     * AC 1: Tambah catatan pemeriksaan. Triase Merah memicu notifikasi ke koordinator posko.
     *
     * @param  array<string, mixed>  $data
     */
    public function createHealthCheck(User $user, array $data): HealthCheck
    {
        $this->ensureIsNakes($user);

        $refugee = Refugee::with('shelter.coordinator')->find((int) ($data['refugee_id'] ?? 0));

        if (! $refugee || ! $refugee->isOccupyingShelter()) {
            throw ValidationException::withMessages([
                'refugee_id' => ['Pengungsi harus terdaftar dan masih berada di posko untuk diperiksa.'],
            ]);
        }

        $payload = $this->normalizePayload($data);
        $payload['checked_at'] ??= now();

        $check = DB::transaction(function () use ($user, $refugee, $payload) {
            $check = HealthCheck::create(array_merge($payload, [
                'refugee_id' => $refugee->id,
                'nakes_id' => $user->id,
            ]));

            ActivityLog::record($user->id, $check, 'created_health_check', [
                'refugee_id' => $refugee->id,
                'shelter_id' => $refugee->shelter_id,
                'triage' => $check->triage,
            ]);

            return $check;
        });

        if ($check->isCritical()) {
            $this->notifyCoordinator($check);
        }

        return $check->fresh(['refugee.shelter', 'nakes', 'referrals']);
    }

    /**
     * AC 3: Koreksi catatan oleh nakes pembuat maksimal 24 jam sejak dibuat; perubahan tercatat di log.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateHealthCheck(User $user, HealthCheck $check, array $data): HealthCheck
    {
        $this->ensureIsNakes($user);
        $this->ensureIsCreator($user, $check, 'mengoreksi');

        if (! $check->isWithinEditWindow()) {
            throw ValidationException::withMessages([
                'health_check' => [
                    'Catatan pemeriksaan hanya dapat dikoreksi maksimal '.HealthCheck::EDIT_WINDOW_HOURS
                        .' jam setelah dibuat (batas: '.$check->editDeadline()->format('d/m/Y H:i').').',
                ],
            ]);
        }

        $payload = $this->normalizePayload($data);
        $wasCritical = $check->isCritical();

        $updated = DB::transaction(function () use ($user, $check, $payload) {
            $before = $this->snapshot($check);
            $check->fill($payload);

            $changes = [];
            foreach (self::EDITABLE_FIELDS as $field) {
                if ($check->isDirty($field)) {
                    $after = $this->snapshot($check)[$field];
                    if ($before[$field] !== $after) {
                        $changes[$field] = ['old' => $before[$field], 'new' => $after];
                    }
                }
            }

            $check->save();

            if (! empty($changes)) {
                ActivityLog::record($user->id, $check, 'updated_health_check', $changes);
            }

            return $check;
        });

        if (! $wasCritical && $updated->isCritical()) {
            $this->notifyCoordinator($updated);
        }

        return $updated->fresh(['refugee.shelter', 'nakes', 'referrals']);
    }

    /**
     * AC 4: Soft delete catatan salah input oleh nakes pembuat dengan alasan,
     * selama belum memiliki rujukan medis.
     */
    public function deleteHealthCheck(User $user, HealthCheck $check, string $deleteReason): HealthCheck
    {
        $this->ensureIsNakes($user);
        $this->ensureIsCreator($user, $check, 'menghapus');

        $cleanReason = trim($deleteReason);
        if ($cleanReason === '') {
            throw ValidationException::withMessages([
                'delete_reason' => ['Alasan penghapusan catatan pemeriksaan wajib diisi.'],
            ]);
        }

        if ($check->hasReferrals()) {
            throw ValidationException::withMessages([
                'delete' => ['Catatan pemeriksaan tidak dapat dihapus karena sudah memiliki rujukan medis.'],
            ]);
        }

        return DB::transaction(function () use ($user, $check, $cleanReason) {
            $check->delete_reason = $cleanReason;
            $check->save();
            $check->delete();

            ActivityLog::record($user->id, $check, 'deleted_health_check', [
                'refugee_id' => $check->refugee_id,
                'triage' => $check->triage,
                'delete_reason' => $cleanReason,
            ]);

            return $check;
        });
    }

    /**
     * AC 2: Ringkasan triase harian per posko.
     *
     * @return array{date: string, totals: array{hijau: int, kuning: int, merah: int, total: int}, shelters: list<array<string, mixed>>}
     */
    public function getDailyTriageSummary(User $user, ?string $date = null, ?int $shelterId = null): array
    {
        $this->ensureCanView($user);

        $day = $date ? Carbon::parse($date) : now();
        $shelters = $this->getAccessibleShelters($user);

        if ($shelterId !== null) {
            $this->ensureCanAccessShelter($user, $shelterId);
            $shelters = $shelters->where('id', $shelterId)->values();
        }

        $rows = DB::table('health_checks')
            ->join('refugees', 'health_checks.refugee_id', '=', 'refugees.id')
            ->whereNull('health_checks.deleted_at')
            ->whereDate('health_checks.checked_at', $day->toDateString())
            ->whereIn('refugees.shelter_id', $shelters->pluck('id')->all())
            ->groupBy('refugees.shelter_id', 'health_checks.triage')
            ->selectRaw('refugees.shelter_id as shelter_id, health_checks.triage as triage, COUNT(*) as total')
            ->get();

        $totals = ['hijau' => 0, 'kuning' => 0, 'merah' => 0, 'total' => 0];
        $perShelter = [];

        foreach ($shelters as $shelter) {
            $perShelter[$shelter->id] = [
                'shelter_id' => $shelter->id,
                'shelter_name' => $shelter->name,
                'hijau' => 0,
                'kuning' => 0,
                'merah' => 0,
                'total' => 0,
            ];
        }

        foreach ($rows as $row) {
            $sid = (int) $row->shelter_id;
            $count = (int) $row->total;
            if (! isset($perShelter[$sid]) || ! isset($totals[$row->triage])) {
                continue;
            }

            $perShelter[$sid][$row->triage] += $count;
            $perShelter[$sid]['total'] += $count;
            $totals[$row->triage] += $count;
            $totals['total'] += $count;
        }

        $unreferredMerah = HealthCheck::query()
            ->whereDate('checked_at', $day->toDateString())
            ->where('triage', 'merah')
            ->forShelters($shelters->pluck('id')->map(fn ($id) => (int) $id)->all())
            ->whereDoesntHave('referrals')
            ->count();

        return [
            'date' => $day->toDateString(),
            'totals' => $totals,
            'unreferred_merah' => $unreferredMerah,
            'shelters' => array_values($perShelter),
        ];
    }

    /**
     * Kirim notifikasi triase Merah ke koordinator posko tempat pengungsi berada.
     */
    protected function notifyCoordinator(HealthCheck $check): void
    {
        $coordinator = $check->refugee?->shelter?->coordinator;

        if ($coordinator instanceof User) {
            $coordinator->notify(new CriticalTriageNotification($check));
        }

        ActivityLog::record($check->nakes_id, $check, 'critical_triage_notified', [
            'coordinator_id' => $coordinator?->id,
            'shelter_id' => $check->refugee?->shelter_id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizePayload(array $data): array
    {
        $payload = [];

        if (array_key_exists('complaint', $data)) {
            $payload['complaint'] = trim((string) $data['complaint']);
        }
        if (array_key_exists('temperature', $data)) {
            $normalizedTemp = str_replace(',', '.', (string) $data['temperature']);
            $payload['temperature'] = round((float) $normalizedTemp, 1);
        }
        if (array_key_exists('blood_pressure', $data)) {
            $payload['blood_pressure'] = str_replace(' ', '', (string) $data['blood_pressure']);
        }
        if (array_key_exists('pulse', $data)) {
            $payload['pulse'] = (int) $data['pulse'];
        }
        if (array_key_exists('triage', $data)) {
            $payload['triage'] = (string) $data['triage'];
        }
        if (array_key_exists('action', $data)) {
            $action = trim((string) $data['action']);
            $payload['action'] = $action !== '' ? $action : null;
        }
        if (! empty($data['checked_at'])) {
            $payload['checked_at'] = Carbon::parse($data['checked_at']);
        }

        return $payload;
    }

    /**
     * Snapshot nilai kolom yang dapat dikoreksi dalam bentuk string agar perbandingan konsisten.
     *
     * @return array<string, string|null>
     */
    protected function snapshot(HealthCheck $check): array
    {
        return [
            'complaint' => $check->complaint,
            'temperature' => $check->temperature !== null ? number_format((float) $check->temperature, 1, '.', '') : null,
            'blood_pressure' => $check->blood_pressure,
            'pulse' => $check->pulse !== null ? (string) $check->pulse : null,
            'triage' => $check->triage,
            'action' => $check->action,
            'checked_at' => $check->checked_at?->format('Y-m-d H:i:s'),
        ];
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

    /**
     * AC 2: Data hanya dapat diakses nakes dan koordinator posko terkait (serta admin Pusdalops).
     */
    protected function ensureCanView(User $user): void
    {
        if (! $user->isNakes() && ! $user->isKoordinator() && ! $user->isAdmin()) {
            abort(403, 'Akses Ditolak: Data pemeriksaan hanya dapat diakses Tenaga Kesehatan dan Koordinator Posko terkait.');
        }
    }

    protected function ensureCanAccessShelter(User $user, int $shelterId): void
    {
        if ($user->isKoordinator() && ! in_array($shelterId, $this->managedShelterIds($user), true)) {
            abort(403, 'Akses Ditolak: Data pemeriksaan ini bukan berasal dari posko kelolaan Anda.');
        }
    }

    protected function ensureIsNakes(User $user): void
    {
        if (! $user->isNakes()) {
            abort(403, 'Akses Ditolak: Hanya Tenaga Kesehatan yang dapat mengelola catatan pemeriksaan.');
        }
    }

    protected function ensureIsCreator(User $user, HealthCheck $check, string $verb): void
    {
        if (! $check->isCreatedBy($user)) {
            abort(403, "Akses Ditolak: Hanya nakes pembuat catatan yang dapat {$verb} catatan pemeriksaan ini.");
        }
    }
}
