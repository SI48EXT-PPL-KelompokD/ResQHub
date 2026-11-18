<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $shelter_id
 * @property string $head_name
 * @property string|null $nik
 * @property int $members
 * @property int $infants
 * @property int $elderly
 * @property int $pregnant
 * @property int $disabled
 * @property string $status
 * @property Carbon|null $checked_in_at
 * @property Carbon|null $checked_out_at
 * @property string|null $delete_reason
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $masked_nik
 * @property-read int $vulnerable_total
 * @property-read string $status_label
 * @property-read Shelter|null $shelter
 * @property-read Collection<int, RefugeeMovement> $movements
 * @property-read Collection<int, HealthCheck> $healthChecks
 * @property-read Collection<int, Distribution> $distributions
 *
 * @method static Builder|Refugee query()
 * @method static Builder|Refugee forShelter(int $shelterId)
 * @method static Builder|Refugee forShelters(array $shelterIds)
 * @method static Builder|Refugee search(?string $search)
 * @method static Builder|Refugee byStatus(?string $status, ?int $shelterId = null)
 */
class Refugee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shelter_id',
        'head_name',
        'nik',
        'members',
        'infants',
        'elderly',
        'pregnant',
        'disabled',
        'status',
        'checked_in_at',
        'checked_out_at',
        'delete_reason',
    ];

    protected $appends = [
        'masked_nik',
        'vulnerable_total',
        'status_label',
    ];

    protected function casts(): array
    {
        return [
            'members' => 'integer',
            'infants' => 'integer',
            'elderly' => 'integer',
            'pregnant' => 'integer',
            'disabled' => 'integer',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
        ];
    }

    public function shelter(): BelongsTo
    {
        return $this->belongsTo(Shelter::class, 'shelter_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(RefugeeMovement::class, 'refugee_id')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');
    }

    public function healthChecks(): HasMany
    {
        return $this->hasMany(HealthCheck::class, 'refugee_id');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class, 'refugee_id');
    }

    /**
     * Format NIK menjadi tersamar (contoh: 3204111205800001 -> 3204********0001).
     */
    public static function maskNik(?string $nik): string
    {
        if (empty($nik)) {
            return '-';
        }

        $clean = trim($nik);
        $length = strlen($clean);

        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($clean, 0, 4) . str_repeat('*', $length - 8) . substr($clean, -4);
    }

    public function getMaskedNikAttribute(): string
    {
        return self::maskNik($this->nik);
    }

    public function getVulnerableTotalAttribute(): int
    {
        return (int) $this->infants
            + (int) $this->elderly
            + (int) $this->pregnant
            + (int) $this->disabled;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'di_posko' => 'Di Posko',
            'pindah' => 'Pindah Posko',
            'pulang' => 'Pulang',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Cek apakah pengungsi masih menghuni posko saat ini (belum pulang).
     */
    public function isOccupyingShelter(): bool
    {
        return in_array($this->status, ['di_posko', 'pindah'], true);
    }

    /**
     * Tentukan status kontekstual pengungsi pada posko yang sedang dilihat:
     * - Di posko lama (shelter_id != $shelterId): berstatus 'pindah' (sebagai catatan riwayat).
     * - Di posko saat ini / terbaru (shelter_id == $shelterId): berstatus 'di_posko' (aktif) atau 'pulang'.
     */
    public function effectiveStatusForShelter(?int $shelterId = null): string
    {
        if ($shelterId !== null && (int) $this->shelter_id !== (int) $shelterId) {
            return 'pindah';
        }

        if ($this->status === 'pulang') {
            return 'pulang';
        }

        return 'di_posko';
    }

    /**
     * Cek apakah pengungsi memiliki catatan pemeriksaan kesehatan.
     */
    public function hasHealthChecks(): bool
    {
        return DB::table('health_checks')
            ->where('refugee_id', $this->id)
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * Cek apakah pengungsi memiliki catatan penyaluran bantuan.
     */
    public function hasDistributions(): bool
    {
        return DB::table('distributions')
            ->where('refugee_id', $this->id)
            ->exists();
    }

    /**
     * AC 4: Data pengungsi hanya dapat dihapus bila belum memiliki
     * catatan penyaluran bantuan atau pemeriksaan kesehatan.
     */
    public function canBeDeleted(): bool
    {
        return !$this->hasHealthChecks() && !$this->hasDistributions();
    }

    /**
     * Daftar alasan mengapa data pengungsi tidak dapat dihapus.
     *
     * @return list<string>
     */
    public function deletionBlockReasons(): array
    {
        $reasons = [];

        if ($this->hasHealthChecks()) {
            $reasons[] = 'pemeriksaan kesehatan';
        }

        if ($this->hasDistributions()) {
            $reasons[] = 'penyaluran bantuan';
        }

        return $reasons;
    }

    /**
     * Scope filter pengungsi berdasarkan posko:
     * Mencakup pengungsi yang saat ini berada di posko tersebut (aktif/pulang)
     * maupun pengungsi yang pernah berada di posko tersebut lalu pindah keluar (riwayat pindah).
     */
    public function scopeForShelter(Builder $query, int $shelterId): Builder
    {
        return $query->where(function (Builder $q) use ($shelterId) {
            $q->where('shelter_id', $shelterId)
                ->orWhereHas('movements', function (Builder $mq) use ($shelterId) {
                    $mq->where('type', 'pindah')
                        ->where('from_shelter_id', $shelterId);
                });
        });
    }

    /**
     * Scope filter pengungsi berdasarkan sejumlah posko kelolaan.
     *
     * @param list<int> $shelterIds
     */
    public function scopeForShelters(Builder $query, array $shelterIds): Builder
    {
        return $query->where(function (Builder $q) use ($shelterIds) {
            $q->whereIn('shelter_id', $shelterIds)
                ->orWhereHas('movements', function (Builder $mq) use ($shelterIds) {
                    $mq->where('type', 'pindah')
                        ->whereIn('from_shelter_id', $shelterIds);
                });
        });
    }

    /**
     * Scope pencarian berdasarkan nama kepala keluarga atau NIK.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        $term = trim($search);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('head_name', 'like', '%' . $term . '%')
                ->orWhere('nik', 'like', '%' . $term . '%');
        });
    }

    /**
     * Scope filter berdasarkan status ('di_posko', 'pindah', 'pulang') secara kontekstual per posko.
     */
    public function scopeByStatus(Builder $query, ?string $status, ?int $shelterId = null): Builder
    {
        if (empty($status)) {
            return $query;
        }

        if ($shelterId !== null) {
            return match ($status) {
                'di_posko' => $query->where('shelter_id', $shelterId)
                    ->whereIn('status', ['di_posko', 'pindah']),
                'pindah' => $query->where('shelter_id', '!=', $shelterId)
                    ->whereHas('movements', function (Builder $mq) use ($shelterId) {
                        $mq->where('type', 'pindah')
                            ->where('from_shelter_id', $shelterId);
                    }),
                'pulang' => $query->where('shelter_id', $shelterId)
                    ->where('status', 'pulang'),
                default => $query->where('status', $status),
            };
        }

        return match ($status) {
            'di_posko' => $query->whereIn('status', ['di_posko', 'pindah']),
            'pindah' => $query->where('status', 'pindah'),
            'pulang' => $query->where('status', 'pulang'),
            default => $query->where('status', $status),
        };
    }
}

