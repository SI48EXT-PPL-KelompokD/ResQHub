<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\EarlyWarning;
use App\Models\User;
use App\Models\WarningUpdate;
use App\Notifications\EarlyWarningNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class EarlyWarningService
{
    /**
     * AC 1: Admin membuat peringatan sebagai draf atau langsung diterbitkan.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(User $admin, array $data, bool $publish = false): EarlyWarning
    {
        $this->authorize($admin, 'create', EarlyWarning::class);

        $warning = DB::transaction(function () use ($admin, $data, $publish) {
            $warning = EarlyWarning::create([
                ...$this->onlyWarningFields($data),
                'issued_by' => $admin->id,
                'status' => $publish ? EarlyWarning::STATUS_ACTIVE : EarlyWarning::STATUS_DRAFT,
            ]);

            ActivityLog::record($admin->id, $warning, $publish ? 'peringatan_diterbitkan' : 'peringatan_draf', [
                'level' => $warning->level,
            ]);

            return $warning;
        });

        if ($publish) {
            $this->notifyRecipients($warning, EarlyWarningNotification::EVENT_ISSUED);
        }

        return $warning;
    }

    /**
     * AC 1: Menerbitkan draf sehingga warga di wilayah sasaran menerima notifikasi.
     */
    public function publish(User $admin, EarlyWarning $warning): EarlyWarning
    {
        $this->authorize($admin, 'publish', $warning);

        $warning->update(['status' => EarlyWarning::STATUS_ACTIVE]);
        ActivityLog::record($admin->id, $warning, 'peringatan_diterbitkan', ['level' => $warning->level]);

        $this->notifyRecipients($warning, EarlyWarningNotification::EVENT_ISSUED);

        return $warning;
    }

    /**
     * AC 2: Daftar peringatan untuk admin dengan filter status dan tingkat.
     *
     * @param  array{status?: string|null, level?: string|null}  $filters
     */
    public function listForAdmin(User $admin, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->authorize($admin, 'viewAny', EarlyWarning::class);

        return EarlyWarning::query()
            ->with(['issuer', 'disasterEvent'])
            ->withCount('updates')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['level'] ?? null, fn ($q, $level) => $q->where('level', $level))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * AC 2: Banner peringatan aktif untuk warga dan pengunjung, tingkat tertinggi di atas.
     * Bila regency diisi, hanya peringatan yang menyasar wilayah tersebut.
     */
    public function activeForPublic(?string $regency = null): Collection
    {
        return EarlyWarning::query()
            ->currentlyActive()
            ->get()
            ->filter(fn (EarlyWarning $warning) => $regency === null
                || empty($warning->target_regencies)
                || in_array($regency, $warning->target_regencies, true))
            ->sortByDesc(fn (EarlyWarning $warning) => EarlyWarning::levelRank($warning->level))
            ->values();
    }

    /**
     * AC 2: Jumlah warga penerima notifikasi untuk ditampilkan di detail peringatan.
     */
    public function recipientCount(EarlyWarning $warning): int
    {
        return $this->recipientsQuery($warning)->count();
    }

    /**
     * AC 3: Admin memperbarui peringatan. Setiap perubahan tercatat di warning_updates,
     * dan kenaikan tingkat pada peringatan aktif memicu notifikasi ulang.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $admin, EarlyWarning $warning, array $data, ?string $note = null): EarlyWarning
    {
        $this->authorize($admin, 'update', $warning);

        $oldLevel = $warning->level;

        $warning = DB::transaction(function () use ($admin, $warning, $data, $note, $oldLevel) {
            $warning->fill($this->onlyWarningFields($data));
            $changes = array_keys($warning->getDirty());
            $warning->save();

            WarningUpdate::create([
                'early_warning_id' => $warning->id,
                'old_level' => $oldLevel,
                'new_level' => $warning->level,
                'note' => $note,
                'updated_by' => $admin->id,
            ]);

            ActivityLog::record($admin->id, $warning, 'peringatan_diubah', ['fields' => $changes]);

            return $warning;
        });

        if ($warning->isActive() && EarlyWarning::levelRank($warning->level) > EarlyWarning::levelRank($oldLevel)) {
            $this->notifyRecipients($warning, EarlyWarningNotification::EVENT_ESCALATED);
        }

        return $warning;
    }

    /**
     * Riwayat pembaruan peringatan, terbaru di atas.
     */
    public function history(EarlyWarning $warning): Collection
    {
        return $warning->updates()->with('updater')->latest('id')->get();
    }

    /**
     * AC 4: Draf dapat dihapus permanen.
     */
    public function deleteDraft(User $admin, EarlyWarning $warning): void
    {
        $this->authorize($admin, 'delete', $warning);

        DB::transaction(function () use ($admin, $warning) {
            ActivityLog::record($admin->id, $warning, 'peringatan_draf_dihapus', ['title' => $warning->title]);
            $warning->delete();
        });
    }

    /**
     * AC 4: Peringatan aktif dicabut dengan alasan wajib, lalu warga terdampak diberi tahu.
     */
    public function revoke(User $admin, EarlyWarning $warning, string $reason): EarlyWarning
    {
        $this->authorize($admin, 'revoke', $warning);

        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'revoke_reason' => 'Alasan pencabutan wajib diisi.',
            ]);
        }

        $warning->update([
            'status' => EarlyWarning::STATUS_REVOKED,
            'revoke_reason' => trim($reason),
        ]);

        ActivityLog::record($admin->id, $warning, 'peringatan_dicabut', ['revoke_reason' => $warning->revoke_reason]);

        $this->notifyRecipients($warning, EarlyWarningNotification::EVENT_REVOKED);

        return $warning;
    }

    /**
     * AC 4: Peringatan aktif yang melewati masa berlaku menjadi kedaluwarsa (dijalankan scheduler).
     */
    public function expireOverdue(): int
    {
        return EarlyWarning::query()
            ->where('status', EarlyWarning::STATUS_ACTIVE)
            ->where('ends_at', '<', now())
            ->update(['status' => EarlyWarning::STATUS_EXPIRED, 'updated_at' => now()]);
    }

    /**
     * Penerima notifikasi: warga yang terdaftar di kabupaten/kota sasaran.
     * Data pengguna belum menyimpan koordinat, sehingga sasaran radius belum dipakai untuk penerima.
     */
    private function recipientsQuery(EarlyWarning $warning)
    {
        return User::query()
            ->where('role', 'warga')
            ->whereIn('regency', $warning->target_regencies ?? []);
    }

    private function notifyRecipients(EarlyWarning $warning, string $event): void
    {
        $recipients = $this->recipientsQuery($warning)->get();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new EarlyWarningNotification($warning, $event));
        }
    }

    private function authorize(User $user, string $ability, mixed $target): void
    {
        if ($user->cannot($ability, $target)) {
            abort(403, 'Akses Ditolak: Hanya Admin Pusdalops yang dapat mengelola peringatan dini pada status ini.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyWarningFields(array $data): array
    {
        return array_intersect_key($data, array_flip([
            'disaster_event_id',
            'title',
            'hazard_type',
            'level',
            'target_regencies',
            'center_lat',
            'center_lng',
            'radius_km',
            'instruction',
            'starts_at',
            'ends_at',
        ]));
    }
}
