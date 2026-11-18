<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\DisasterEvent;
use App\Models\DisasterReport;
use App\Models\User;
use App\Notifications\DisasterReportVerifiedNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DisasterReportService
{
    public const DUPLICATE_RADIUS_KM = 5;

    private const PHOTO_DIRECTORY = 'report-photos';

    /**
     * AC 1: Warga membuat laporan baru berstatus menunggu verifikasi.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $photos
     */
    public function create(User $reporter, array $data, array $photos = []): DisasterReport
    {
        $this->ensurePhotoLimit(count($photos));

        return DB::transaction(function () use ($reporter, $data, $photos) {
            $report = DisasterReport::create([
                ...$this->onlyReportFields($data),
                'reporter_id' => $reporter->id,
                'status' => DisasterReport::STATUS_PENDING,
            ]);

            $this->storePhotos($report, $photos);

            ActivityLog::record($reporter->id, $report, 'laporan_dibuat', ['status' => $report->status]);

            return $report->load('photos');
        });
    }

    /**
     * AC 2: Daftar laporan milik warga, terbaru di atas.
     */
    public function listForReporter(User $reporter, int $perPage = 10): LengthAwarePaginator
    {
        return DisasterReport::query()
            ->ownedBy($reporter->id)
            ->with('photos')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * AC 2: Antrean dan seluruh laporan untuk admin dengan filter jenis, status, wilayah, dan tanggal.
     *
     * @param  array{disaster_type?: string|null, status?: string|null, area?: string|null, date_from?: string|null, date_to?: string|null}  $filters
     */
    public function listForAdmin(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return DisasterReport::query()
            ->with(['reporter', 'photos'])
            ->when($filters['disaster_type'] ?? null, fn ($q, $type) => $q->where('disaster_type', $type))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['area'] ?? null, fn ($q, $area) => $q->where('address', 'like', '%'.$area.'%'))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('occurred_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('occurred_at', '<=', $date))
            ->orderByRaw('case when status = ? then 0 else 1 end', [DisasterReport::STATUS_PENDING])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * AC 2: Laporan lain dalam radius 5 km sebagai indikasi duplikat.
     * Jarak dihitung di PHP (Haversine) agar sama hasilnya di SQLite dan MySQL.
     */
    public function nearbyReports(DisasterReport $report, float $radiusKm = self::DUPLICATE_RADIUS_KM): Collection
    {
        $latDelta = $radiusKm / 111;
        $lngDelta = $radiusKm / (111 * max(cos(deg2rad($report->lat)), 0.01));

        return DisasterReport::query()
            ->whereKeyNot($report->id)
            ->whereBetween('lat', [$report->lat - $latDelta, $report->lat + $latDelta])
            ->whereBetween('lng', [$report->lng - $lngDelta, $report->lng + $lngDelta])
            ->get()
            ->map(function (DisasterReport $other) use ($report) {
                $other->distance_km = round($this->distanceKm($report->lat, $report->lng, $other->lat, $other->lng), 2);

                return $other;
            })
            ->filter(fn (DisasterReport $other) => $other->distance_km <= $radiusKm)
            ->sortBy('distance_km')
            ->values();
    }

    /**
     * AC 3: Warga mengubah isi, lokasi, dan foto selama laporan masih menunggu verifikasi.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $newPhotos
     * @param  array<int, int>  $removedPhotoIds
     */
    public function update(User $reporter, DisasterReport $report, array $data, array $newPhotos = [], array $removedPhotoIds = []): DisasterReport
    {
        $this->ensureEditable($reporter, $report);

        $removed = $report->photos()->whereIn('id', $removedPhotoIds)->get();
        $remaining = $report->photos()->count() - $removed->count();
        $this->ensurePhotoLimit($remaining + count($newPhotos));

        return DB::transaction(function () use ($reporter, $report, $data, $newPhotos, $removed) {
            $report->fill($this->onlyReportFields($data));
            $changes = $report->getDirty();
            $report->save();

            foreach ($removed as $photo) {
                Storage::disk('public')->delete($photo->path);
                $photo->delete();
            }

            $this->storePhotos($report, $newPhotos);

            ActivityLog::record($reporter->id, $report, 'laporan_diubah', [
                'fields' => array_keys($changes),
                'photos_added' => count($newPhotos),
                'photos_removed' => $removed->count(),
            ]);

            return $report->load('photos');
        });
    }

    /**
     * AC 3: Admin menerima laporan dan mengaitkannya ke kejadian yang ada atau kejadian baru.
     *
     * @param  array{name: string, severity: string, regency: string}|null  $newEvent
     */
    public function accept(User $admin, DisasterReport $report, ?int $disasterEventId = null, ?array $newEvent = null): DisasterReport
    {
        $this->ensureVerifiable($admin, $report);

        if (! $disasterEventId && empty($newEvent['name'])) {
            throw ValidationException::withMessages([
                'disaster_event_id' => 'Pilih kejadian yang ada atau isi data kejadian baru.',
            ]);
        }

        $report = DB::transaction(function () use ($admin, $report, $disasterEventId, $newEvent) {
            $event = $disasterEventId
                ? DisasterEvent::findOrFail($disasterEventId)
                : DisasterEvent::create([
                    'name' => $newEvent['name'],
                    'disaster_type' => $report->disaster_type,
                    'severity' => $newEvent['severity'],
                    'status' => 'aktif',
                    'regency' => $newEvent['regency'],
                    'lat' => $report->lat,
                    'lng' => $report->lng,
                    'started_at' => $report->occurred_at,
                ]);

            $report->update([
                'status' => DisasterReport::STATUS_ACCEPTED,
                'disaster_event_id' => $event->id,
                'reject_reason' => null,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);

            ActivityLog::record($admin->id, $report, 'laporan_diterima', [
                'disaster_event_id' => $event->id,
                'kejadian_baru' => ! $disasterEventId,
            ]);

            return $report;
        });

        $report->reporter->notify(new DisasterReportVerifiedNotification($report));

        return $report;
    }

    /**
     * AC 3: Admin menolak laporan dengan alasan wajib.
     */
    public function reject(User $admin, DisasterReport $report, string $reason): DisasterReport
    {
        $this->ensureVerifiable($admin, $report);

        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'reject_reason' => 'Alasan penolakan wajib diisi.',
            ]);
        }

        $report->update([
            'status' => DisasterReport::STATUS_REJECTED,
            'reject_reason' => trim($reason),
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        ActivityLog::record($admin->id, $report, 'laporan_ditolak', ['reject_reason' => $report->reject_reason]);

        $report->reporter->notify(new DisasterReportVerifiedNotification($report));

        return $report;
    }

    /**
     * AC 4: Warga menghapus laporan yang belum diverifikasi beserta fotonya.
     */
    public function delete(User $reporter, DisasterReport $report): void
    {
        $this->ensureEditable($reporter, $report);

        DB::transaction(function () use ($reporter, $report) {
            Storage::disk('public')->delete($report->photos->pluck('path')->all());

            ActivityLog::record($reporter->id, $report, 'laporan_dihapus', ['title' => $report->title]);

            $report->delete();
        });
    }

    /**
     * Riwayat status laporan dari activity_logs.
     */
    public function history(DisasterReport $report): Collection
    {
        return ActivityLog::query()
            ->with('user')
            ->where('subject_type', $report->getMorphClass())
            ->where('subject_id', $report->id)
            ->orderBy('id')
            ->get();
    }

    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function ensureEditable(User $user, DisasterReport $report): void
    {
        if ($user->cannot('update', $report)) {
            abort(403, 'Laporan hanya dapat diubah atau dihapus oleh pelapor selama masih menunggu verifikasi.');
        }
    }

    private function ensureVerifiable(User $user, DisasterReport $report): void
    {
        if ($user->cannot('verify', $report)) {
            abort(403, 'Hanya Admin Pusdalops yang dapat memverifikasi laporan yang masih menunggu.');
        }
    }

    private function ensurePhotoLimit(int $total): void
    {
        if ($total > DisasterReport::MAX_PHOTOS) {
            throw ValidationException::withMessages([
                'photos' => 'Maksimal '.DisasterReport::MAX_PHOTOS.' foto per laporan.',
            ]);
        }
    }

    /**
     * @param  array<int, UploadedFile>  $photos
     */
    private function storePhotos(DisasterReport $report, array $photos): void
    {
        foreach ($photos as $photo) {
            $report->photos()->create([
                'path' => $photo->store(self::PHOTO_DIRECTORY.'/'.$report->id, 'public'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyReportFields(array $data): array
    {
        return array_intersect_key($data, array_flip([
            'disaster_type',
            'title',
            'description',
            'estimated_affected',
            'lat',
            'lng',
            'address',
            'occurred_at',
        ]));
    }
}
