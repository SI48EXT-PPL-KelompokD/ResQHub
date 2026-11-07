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
            'penuh' => 'Penuh',
            'ditutup' => 'Ditutup',
            default => ucfirst($this->status),
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
