<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisasterReport extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'menunggu';

    public const STATUS_ACCEPTED = 'diterima';

    public const STATUS_REJECTED = 'ditolak';

    public const DISASTER_TYPES = [
        'banjir',
        'tanah_longsor',
        'gempa_bumi',
        'angin_puting_beliung',
        'cuaca_ekstrem',
        'kebakaran_hutan',
        'tsunami',
        'gunung_meletus',
        'kekeringan',
        'lainnya',
    ];

    public const MAX_PHOTOS = 3;

    protected $fillable = [
        'reporter_id',
        'disaster_event_id',
        'disaster_type',
        'title',
        'description',
        'estimated_affected',
        'lat',
        'lng',
        'address',
        'occurred_at',
        'status',
        'reject_reason',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'estimated_affected' => 'integer',
            'occurred_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function disasterEvent(): BelongsTo
    {
        return $this->belongsTo(DisasterEvent::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ReportPhoto::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('reporter_id', $userId);
    }
}
