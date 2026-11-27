<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property int $health_check_id
 * @property string $facility_name
 * @property string $reason
 * @property string $patient_condition
 * @property bool $transport_needed
 * @property string $status
 * @property string|null $outcome
 * @property string|null $cancel_reason
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $status_label
 * @property-read string|null $outcome_label
 * @property-read HealthCheck|null $healthCheck
 * @property-read User|null $creator
 * @property-read Collection<int, ActivityLog> $activityLogs
 *
 * @method static Builder|MedicalReferral query()
 * @method static Builder|MedicalReferral forShelters(array $shelterIds)
 * @method static Builder|MedicalReferral notCancelled()
 * @method static Builder|MedicalReferral active()
 */
class MedicalReferral extends Model
{
    use HasFactory;

    public const STATUS_DIAJUKAN = 'diajukan';

    public const STATUS_DALAM_PERJALANAN = 'dalam_perjalanan';

    public const STATUS_DITERIMA_FASKES = 'diterima_faskes';

    public const STATUS_SELESAI = 'selesai';

    public const STATUS_DIBATALKAN = 'dibatalkan';

    /**
     * Seluruh status valid rujukan medis.
     *
     * @var list<string>
     */
    public const STATUSES = [
        self::STATUS_DIAJUKAN,
        self::STATUS_DALAM_PERJALANAN,
        self::STATUS_DITERIMA_FASKES,
        self::STATUS_SELESAI,
        self::STATUS_DIBATALKAN,
    ];

    /**
     * Status rujukan yang masih berjalan (belum selesai & belum dibatalkan).
     *
     * @var list<string>
     */
    public const ACTIVE_STATUSES = [
        self::STATUS_DIAJUKAN,
        self::STATUS_DALAM_PERJALANAN,
        self::STATUS_DITERIMA_FASKES,
    ];

    /**
     * Status target yang dapat dipilih pada pembaruan perkembangan rujukan.
     *
     * @var list<string>
     */
    public const UPDATABLE_STATUSES = [
        self::STATUS_DIAJUKAN,
        self::STATUS_DALAM_PERJALANAN,
        self::STATUS_DITERIMA_FASKES,
        self::STATUS_SELESAI,
    ];

    public const OUTCOME_KEMBALI_KE_POSKO = 'kembali_ke_posko';

    public const OUTCOME_DIRAWAT = 'dirawat';

    /**
     * Pilihan hasil akhir wajib ketika status rujukan menjadi Selesai.
     *
     * @var list<string>
     */
    public const OUTCOMES = [
        self::OUTCOME_KEMBALI_KE_POSKO,
        self::OUTCOME_DIRAWAT,
    ];

    protected $fillable = [
        'health_check_id',
        'facility_name',
        'reason',
        'patient_condition',
        'transport_needed',
        'status',
        'outcome',
        'cancel_reason',
        'created_by',
    ];

    protected $appends = [
        'status_label',
        'outcome_label',
    ];

    protected function casts(): array
    {
        return [
            'transport_needed' => 'boolean',
        ];
    }

    public function healthCheck(): BelongsTo
    {
        return $this->belongsTo(HealthCheck::class, 'health_check_id')->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Riwayat perubahan status dan catatan perkembangan rujukan dari activity_logs.
     */
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject')
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_DALAM_PERJALANAN => 'Dalam Perjalanan',
            self::STATUS_DITERIMA_FASKES => 'Diterima Faskes',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_DIBATALKAN => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', (string) $status)),
        };
    }

    public static function outcomeLabel(?string $outcome): ?string
    {
        if ($outcome === null || $outcome === '') {
            return null;
        }

        return match ($outcome) {
            self::OUTCOME_KEMBALI_KE_POSKO => 'Kembali ke posko',
            self::OUTCOME_DIRAWAT => 'Dirawat di faskes',
            default => $outcome,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabel($this->status);
    }

    public function getOutcomeLabelAttribute(): ?string
    {
        return self::outcomeLabel($this->outcome);
    }

    public function isDiajukan(): bool
    {
        return $this->status === self::STATUS_DIAJUKAN;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_SELESAI;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_DIBATALKAN;
    }

    public function isOngoing(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    /**
     * AC 4: Rujukan hanya dapat dibatalkan saat masih berstatus Diajukan.
     */
    public function canBeCancelled(): bool
    {
        return $this->status === self::STATUS_DIAJUKAN;
    }

    /**
     * AC 3: Status dan catatan perkembangan hanya dapat diperbarui selama belum Selesai/Dibatalkan.
     */
    public function canUpdateStatus(): bool
    {
        return ! in_array($this->status, [self::STATUS_SELESAI, self::STATUS_DIBATALKAN], true);
    }

    /**
     * Daftar status tujuan yang diperbolehkan dari status saat ini (tidak boleh mundur).
     *
     * @return list<string>
     */
    public function allowedNextStatuses(): array
    {
        return match ($this->status) {
            self::STATUS_DIAJUKAN => [
                self::STATUS_DIAJUKAN,
                self::STATUS_DALAM_PERJALANAN,
                self::STATUS_DITERIMA_FASKES,
                self::STATUS_SELESAI,
            ],
            self::STATUS_DALAM_PERJALANAN => [
                self::STATUS_DALAM_PERJALANAN,
                self::STATUS_DITERIMA_FASKES,
                self::STATUS_SELESAI,
            ],
            self::STATUS_DITERIMA_FASKES => [
                self::STATUS_DITERIMA_FASKES,
                self::STATUS_SELESAI,
            ],
            default => [],
        };
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        return in_array($targetStatus, $this->allowedNextStatuses(), true);
    }

    /**
     * Scope filter rujukan berdasarkan posko pengungsi dari pemeriksaan asal.
     *
     * @param  list<int>  $shelterIds
     */
    public function scopeForShelters(Builder $query, array $shelterIds): Builder
    {
        return $query->whereHas('healthCheck', function (Builder $hcQuery) use ($shelterIds) {
            $hcQuery->withTrashed()->whereHas('refugee', function (Builder $refugeeQuery) use ($shelterIds) {
                $refugeeQuery->withTrashed()->whereIn('shelter_id', $shelterIds);
            });
        });
    }

    /**
     * Scope rujukan yang tidak dibatalkan.
     */
    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_DIBATALKAN);
    }

    /**
     * Scope rujukan yang masih aktif berjalan.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }
}

