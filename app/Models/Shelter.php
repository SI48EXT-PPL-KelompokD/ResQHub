<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $disaster_event_id
 * @property int $coordinator_id
 * @property string $name
 * @property string $address
 * @property float $lat
 * @property float $lng
 * @property int $capacity
 * @property int $occupancy
 * @property string $contact
 * @property string $status
 * @property string|null $close_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $coordinator
 * @property-read Collection<int, Refugee> $refugees
 * @property-read Collection<int, Refugee> $activeRefugees
 * @property-read Collection<int, RefugeeMovement> $outgoingMovements
 * @property-read Collection<int, RefugeeMovement> $incomingMovements
 * @property-read Collection<int, Distribution> $distributions
 *
 * @method static Builder|Shelter query()
 */
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
     * Seluruh data pengungsi pada posko ini.
     */
    public function refugees(): HasMany
    {
        return $this->hasMany(Refugee::class, 'shelter_id');
    }

    /**
     * Data pengungsi yang masih aktif pada posko ini.
     */
    public function activeRefugees(): HasMany
    {
        return $this->hasMany(Refugee::class, 'shelter_id')
            ->whereIn('status', ['di_posko', 'pindah']);
    }

    /**
     * Riwayat perpindahan pengungsi yang keluar dari posko ini.
     */
    public function outgoingMovements(): HasMany
    {
        return $this->hasMany(RefugeeMovement::class, 'from_shelter_id');
    }

    /**
     * Riwayat perpindahan pengungsi yang masuk ke posko ini.
     */
    public function incomingMovements(): HasMany
    {
        return $this->hasMany(RefugeeMovement::class, 'to_shelter_id');
    }

    /**
     * Riwayat penyaluran bantuan dari posko ini.
     */
    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class, 'shelter_id');
    }

    /**
     * Kebutuhan bantuan yang diajukan posko ini.
     */
    public function aidNeeds(): HasMany
    {
        return $this->hasMany(AidNeed::class, 'shelter_id');
    }

    /**
     * Stok logistik yang tersimpan di posko ini.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'shelter_id');
    }

    /**
     * Fasilitas yang tersedia di posko ini.
     */
    public function facilities(): HasMany
    {
        return $this->hasMany(ShelterFacility::class, 'shelter_id');
    }

    /**
     * Jumlah fasilitas yang berstatus "Tidak Berfungsi" pada posko ini.
     */
    public function getBrokenFacilitiesCountAttribute(): int
    {
        if ($this->relationLoaded('facilities')) {
            return $this->facilities->where('condition', 'tidak_berfungsi')->count();
        }

        return $this->facilities()->broken()->count();
    }

    /**
     * Jumlah fasilitas yang perlu perhatian (Rusak Ringan / Tidak Berfungsi).
     */
    public function getNeedsAttentionFacilitiesCountAttribute(): int
    {
        if ($this->relationLoaded('facilities')) {
            return $this->facilities->whereIn('condition', ['rusak_ringan', 'tidak_berfungsi'])->count();
        }

        return $this->facilities()->needsAttention()->count();
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
     * Sisa kapasitas posko yang tersedia.
     */
    public function availableCapacity(): int
    {
        return max(0, $this->capacity - $this->occupancy);
    }

    /**
     * Cek apakah posko memiliki kapasitas yang cukup untuk jumlah jiwa tertentu.
     */
    public function hasCapacityFor(int $members): bool
    {
        if ($this->status === 'ditutup') {
            return false;
        }

        return ($this->occupancy + $members) <= $this->capacity;
    }

    /**
     * Cek apakah penambahan anggota akan melebihi kapasitas maksimal posko.
     */
    public function wouldExceedCapacity(int $additionalMembers): bool
    {
        return ($this->occupancy + $additionalMembers) > $this->capacity;
    }

    /**
     * Perbarui okupansi posko secara otomatis dan sinkronkan status aktif/penuh.
     */
    public function adjustOccupancy(int $delta): void
    {
        $newOccupancy = max(0, $this->occupancy + $delta);
        $this->occupancy = $newOccupancy;

        if ($this->status !== 'ditutup') {
            $this->status = $newOccupancy >= $this->capacity ? 'penuh' : 'aktif';
        }

        $this->save();
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

        $percentage = round(($this->occupancy / $this->capacity) * 100, 1);

        // Posko yang belum benar-benar penuh tidak boleh tampil 100% akibat pembulatan
        return $this->occupancy < $this->capacity ? min(99.9, $percentage) : $percentage;
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

        if ($this->status === 'penuh' || $this->occupancy >= $this->capacity) {
            return 'penuh';
        }

        // Memakai rasio asli (tanpa pembulatan) agar selaras dengan scopeCapacityStatus
        if (($this->occupancy / $this->capacity) >= 0.8) {
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
     * Scope posko yang masih beroperasi (tidak ditutup).
     */
    public function scopeOperational(Builder $query): Builder
    {
        return $query->where('status', '!=', 'ditutup');
    }

    /**
     * Scope kalkulasi jarak terdekat berdasarkan rumus Haversine (dalam kilometer).
     */
    public function scopeNearby(Builder $query, float $lat, float $lng, ?float $radiusKm = null): Builder
    {
        $connection = $query->getConnection();
        if ($connection->getDriverName() === 'sqlite') {
            $pdo = $connection->getPdo();
            if (method_exists($pdo, 'sqliteCreateFunction')) {
                $pdo->sqliteCreateFunction('acos', function ($val) {
                    return acos(max(-1.0, min(1.0, (float) $val)));
                }, 1);
                $pdo->sqliteCreateFunction('cos', 'cos', 1);
                $pdo->sqliteCreateFunction('sin', 'sin', 1);
                $pdo->sqliteCreateFunction('radians', 'deg2rad', 1);
            }
        }

        $cosine = 'cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat))';

        // Pembulatan floating point bisa menghasilkan nilai di luar rentang acos (-1 s.d. 1)
        // saat titik acuan sama dengan titik posko. Fungsi acos SQLite sudah dibatasi di atas.
        $boundedCosine = $connection->getDriverName() === 'sqlite'
            ? $cosine
            : "LEAST(1, GREATEST(-1, {$cosine}))";

        // 6371 adalah radius rata-rata bumi dalam kilometer
        $haversine = "(6371 * acos({$boundedCosine}))";

        $query->select('*')
            ->selectRaw("{$haversine} AS distance", [$lat, $lng, $lat])
            ->orderBy('distance', 'asc');

        if ($radiusKm !== null && (float) $radiusKm > 0) {
            $radiusVal = (float) $radiusKm;
            $query->whereRaw("{$haversine} <= {$radiusVal}", [$lat, $lng, $lat]);
        }

        return $query;
    }

    /**
     * Hitung jarak dua titik koordinat bumi dalam kilometer (rumus Haversine murni PHP).
     */
    public static function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * URL tautan Google Maps untuk navigasi langsung ke titik lokasi posko.
     */
    public function getGoogleMapsUrlAttribute(): string
    {
        return "https://www.google.com/maps/dir/?api=1&destination={$this->lat},{$this->lng}";
    }

    /**
     * Nomor telepon yang dapat dipanggil dari kolom kontak (misal: "0812345 (Pak Budi)" menjadi "0812345").
     */
    public function getContactPhoneAttribute(): ?string
    {
        if (! preg_match('/\+?\d[\d\s\-]{5,}\d/', (string) $this->contact, $matches)) {
            return null;
        }

        return preg_replace('/[\s\-]/', '', $matches[0]);
    }

    /**
     * Representasi jarak terformat (misal: "850 m" atau "2.4 km").
     */
    public function getFormattedDistanceAttribute(): string
    {
        if (! isset($this->attributes['distance'])) {
            return '-';
        }

        $dist = (float) $this->attributes['distance'];
        if ($dist < 1.0) {
            return round($dist * 1000) . ' m';
        }

        return number_format($dist, 1, ',', '.') . ' km';
    }
}
