<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EarlyWarning extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draf';

    public const STATUS_ACTIVE = 'aktif';

    public const STATUS_EXPIRED = 'kedaluwarsa';

    public const STATUS_REVOKED = 'dicabut';

    /**
     * Urutan tingkat peringatan dari terendah ke tertinggi.
     */
    public const LEVELS = ['waspada', 'siaga', 'awas'];

    protected $fillable = [
        'disaster_event_id',
        'issued_by',
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
        'status',
        'revoke_reason',
    ];

    protected function casts(): array
    {
        return [
            'target_regencies' => 'array',
            'center_lat' => 'float',
            'center_lng' => 'float',
            'radius_km' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function disasterEvent(): BelongsTo
    {
        return $this->belongsTo(DisasterEvent::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(WarningUpdate::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public static function levelRank(string $level): int
    {
        return (int) array_search($level, self::LEVELS, true);
    }

    /**
     * Peringatan aktif yang sedang berlaku untuk ditampilkan ke warga.
     */
    public function scopeCurrentlyActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }
}
