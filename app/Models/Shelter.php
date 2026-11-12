<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Shelter extends Model
{
    use HasFactory;

    protected $table = 'shelters';

    protected $fillable = [
        'disaster_event_id',
        'coordinator_id',
        'name',
        'address',
        'lat',
        'lng',
        'capacity',
        'occupancy',
        'contact',
        'status',
        'close_reason',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'capacity' => 'integer',
        'occupancy' => 'integer',
    ];

    /**
     * Kejadian bencana terkait posko ini.
     */
    public function disasterEvent(): BelongsTo
    {
        return $this->belongsTo(DisasterEvent::class, 'disaster_event_id');
    }

    /**
     * Koordinator pengelola posko (User).
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    /**
     * Fasilitas yang tersedia di posko ini.
     */
    public function facilities(): HasMany
    {
        return $this->hasMany(ShelterFacility::class, 'shelter_id');
    }

    /**
     * Memeriksa apakah status posko dapat diubah menjadi "Ditutup".
     * Aturan bisnis PBI-04:
     * - Status "Ditutup" hanya dapat dipilih bila tidak ada pengungsi aktif (occupancy == 0).
     */
    public function canBeClosed(): bool
    {
        return (int) $this->occupancy === 0;
    }

    /**
     * Memeriksa apakah posko dapat dihapus secara permanen.
     * Aturan bisnis PBI-04:
     * - Posko hanya dapat dihapus bila belum pernah memiliki pengungsi, kebutuhan, atau stok logistik.
     * - Bila sudah memiliki riwayat data tersebut, posko hanya dapat ditutup.
     */
    public function canBeDeleted(): bool
    {
        if ((int) $this->occupancy > 0) {
            return false;
        }

        if (DB::table('refugees')->where('shelter_id', $this->id)->exists()) {
            return false;
        }

        if (DB::table('aid_needs')->where('shelter_id', $this->id)->exists()) {
            return false;
        }

        if (DB::table('stocks')->where('shelter_id', $this->id)->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Mendapatkan daftar alasan yang memblokir penghapusan posko.
     */
    public function getDeleteBlockReasonsAttribute(): array
    {
        $reasons = [];

        if ((int) $this->occupancy > 0) {
            $reasons[] = "masih menampung {$this->occupancy} jiwa pengungsi aktif";
        } elseif (DB::table('refugees')->where('shelter_id', $this->id)->exists()) {
            $reasons[] = 'memiliki riwayat pencatatan data pengungsi';
        }

        if (DB::table('aid_needs')->where('shelter_id', $this->id)->exists()) {
            $reasons[] = 'memiliki data permohonan kebutuhan logistik';
        }

        if (DB::table('stocks')->where('shelter_id', $this->id)->exists()) {
            $reasons[] = 'memiliki data inventaris/stok bantuan';
        }

        return $reasons;
    }

    /**
     * Sisa kapasitas tampung jiwa yang masih tersedia.
     */
    public function getRemainingCapacityAttribute(): int
    {
        return max(0, $this->capacity - $this->occupancy);
    }

    /**
     * Persentase okupansi posko saat ini (0 - 100%).
     */
    public function getOccupancyPercentageAttribute(): float
    {
        if ($this->capacity <= 0) {
            return 0.0;
        }

        return round(($this->occupancy / $this->capacity) * 100, 1);
    }

    /**
     * Status kapasitas berdasarkan acceptance criteria:
     * - 'ditutup' jika status posko ditutup
     * - 'penuh' jika okupansi >= kapasitas
     * - 'hampir_penuh' jika okupansi 80% s.d. 99%
     * - 'tersedia' jika okupansi < 80%
     */
    public function getCapacityStatusAttribute(): string
    {
        if ($this->status === 'ditutup') {
            return 'ditutup';
        }

        $percentage = $this->occupancy_percentage;

        if ($percentage >= 100 || $this->status === 'penuh') {
            return 'penuh';
        }

        if ($percentage >= 80) {
            return 'hampir_penuh';
        }

        return 'tersedia';
    }

    /**
     * Label representasi status kapasitas untuk antarmuka pengguna.
     */
    public function getCapacityStatusLabelAttribute(): string
    {
        return match ($this->capacity_status) {
            'tersedia' => 'Tersedia (<80%)',
            'hampir_penuh' => 'Hampir Penuh (80-99%)',
            'penuh' => 'Penuh (100%)',
            'ditutup' => 'Ditutup',
            default => ucfirst($this->status),
        };
    }

    /**
     * Kelas CSS warna badge status kapasitas (Tailwind CSS).
     */
    public function getCapacityBadgeClassesAttribute(): string
    {
        return match ($this->capacity_status) {
            'penuh' => 'bg-red-50 text-red-700 border-red-200',
            'hampir_penuh' => 'bg-amber-50 text-amber-700 border-amber-200',
            'ditutup' => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        };
    }

    /**
     * Scope filter berdasarkan status kapasitas posko evakuasi.
     */
    public function scopeCapacityStatus(Builder $query, string $status): Builder
    {
        return match ($status) {
            'tersedia' => $query->where('status', '!=', 'ditutup')
                                ->where('status', '!=', 'penuh')
                                ->whereRaw('(occupancy * 1.0 / NULLIF(capacity, 0)) < 0.8'),
            'hampir_penuh' => $query->where('status', '!=', 'ditutup')
                                     ->where('status', '!=', 'penuh')
                                     ->whereRaw('(occupancy * 1.0 / NULLIF(capacity, 0)) >= 0.8 AND (occupancy * 1.0 / NULLIF(capacity, 0)) < 1.0'),
            'penuh' => $query->where('status', '!=', 'ditutup')
                             ->where(function (Builder $sub) {
                                 $sub->where('status', 'penuh')
                                     ->orWhereRaw('occupancy >= capacity');
                             }),
            'ditutup' => $query->where('status', 'ditutup'),
            default => $query,
        };
    }

    /**
     * Scope posko kelolaan koordinator tertentu (kecuali jika admin yang mengakses).
     */
    public function scopeForCoordinator(Builder $query, int $userId): Builder
    {
        return $query->where('coordinator_id', $userId);
    }

    /**
     * Scope posko yang berstatus aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope kalkulasi jarak terdekat berdasarkan rumus Haversine (dalam kilometer).
     */
    public function scopeNearby(Builder $query, float $lat, float $lng, ?float $radiusKm = null): Builder
    {
        // 6371 adalah radius rata-rata bumi dalam kilometer
        $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat))))";

        $query->select('*')
            ->selectRaw("{$haversine} AS distance", [$lat, $lng, $lat])
            ->orderBy('distance', 'asc');

        if ($radiusKm !== null) {
            $query->having('distance', '<=', $radiusKm);
        }

        return $query;
    }
}
