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

/**
 * @property int $id
 * @property int $refugee_id
 * @property int $nakes_id
 * @property string $complaint
 * @property string $temperature
 * @property string $blood_pressure
 * @property int $pulse
 * @property string $triage
 * @property string|null $action
 * @property Carbon $checked_at
 * @property string|null $delete_reason
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $triage_label
 * @property-read Refugee|null $refugee
 * @property-read User|null $nakes
 * @property-read Collection<int, MedicalReferral> $referrals
 *
 * @method static Builder|HealthCheck query()
 * @method static Builder|HealthCheck forShelters(array $shelterIds)
 */
class HealthCheck extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * AC 3: Batas waktu koreksi catatan pemeriksaan (jam) sejak dibuat.
     */
    public const EDIT_WINDOW_HOURS = 24;

    public const TRIAGE_LEVELS = ['hijau', 'kuning', 'merah'];

    protected $fillable = [
        'refugee_id',
        'nakes_id',
        'complaint',
        'temperature',
        'blood_pressure',
        'pulse',
        'triage',
        'action',
        'checked_at',
        'delete_reason',
    ];

    protected $appends = [
        'triage_label',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:1',
            'pulse' => 'integer',
            'checked_at' => 'datetime',
        ];
    }

    public function refugee(): BelongsTo
    {
        return $this->belongsTo(Refugee::class, 'refugee_id')->withTrashed();
    }

    public function nakes(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nakes_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(MedicalReferral::class, 'health_check_id');
    }

    public static function triageLabel(?string $triage): string
    {
        return match ($triage) {
            'hijau' => 'Hijau (ringan)',
            'kuning' => 'Kuning (perlu observasi)',
            'merah' => 'Merah (gawat)',
            default => ucfirst((string) $triage),
        };
    }

    public function getTriageLabelAttribute(): string
    {
        return self::triageLabel($this->triage);
    }

    public function isCritical(): bool
    {
        return $this->triage === 'merah';
    }

    public function isCreatedBy(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return (int) $this->nakes_id === (int) $userId;
    }

    /**
     * Batas akhir catatan masih dapat dikoreksi.
     */
    public function editDeadline(): Carbon
    {
        return ($this->created_at ?? now())->copy()->addHours(self::EDIT_WINDOW_HOURS);
    }

    /**
     * AC 3: Catatan hanya dapat dikoreksi maksimal 24 jam setelah dibuat.
     */
    public function isWithinEditWindow(): bool
    {
        return now()->lessThanOrEqualTo($this->editDeadline());
    }

    public function hasReferrals(): bool
    {
        return $this->referrals()->exists();
    }

    /**
     * AC 3: Nakes pembuat dapat mengoreksi selama masih dalam jendela 24 jam.
     */
    public function canBeEditedBy(User $user): bool
    {
        return $user->isNakes()
            && $this->isCreatedBy($user)
            && $this->isWithinEditWindow();
    }

    /**
     * AC 4: Nakes pembuat dapat menghapus selama belum memiliki rujukan medis.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $user->isNakes()
            && $this->isCreatedBy($user)
            && ! $this->hasReferrals();
    }

    /**
     * Scope filter pemeriksaan berdasarkan posko pengungsi saat ini.
     *
     * @param  list<int>  $shelterIds
     */
    public function scopeForShelters(Builder $query, array $shelterIds): Builder
    {
        return $query->whereHas('refugee', function (Builder $q) use ($shelterIds) {
            $q->whereIn('shelter_id', $shelterIds);
        });
    }
}
