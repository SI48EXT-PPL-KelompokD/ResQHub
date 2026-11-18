<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $refugee_id
 * @property int|null $from_shelter_id
 * @property int|null $to_shelter_id
 * @property string $type
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $type_label
 * @property-read Refugee|null $refugee
 * @property-read Shelter|null $fromShelter
 * @property-read Shelter|null $toShelter
 * @property-read User|null $creator
 *
 * @method static Builder|RefugeeMovement query()
 */
class RefugeeMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'refugee_id',
        'from_shelter_id',
        'to_shelter_id',
        'type',
        'created_by',
    ];

    protected $appends = [
        'type_label',
    ];

    public function refugee(): BelongsTo
    {
        return $this->belongsTo(Refugee::class, 'refugee_id')->withTrashed();
    }

    public function fromShelter(): BelongsTo
    {
        return $this->belongsTo(Shelter::class, 'from_shelter_id');
    }

    public function toShelter(): BelongsTo
    {
        return $this->belongsTo(Shelter::class, 'to_shelter_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'masuk' => 'Check-In (Masuk Posko)',
            'pindah' => 'Pindah Posko',
            'pulang' => 'Kepulangan (Pulang)',
            default => ucfirst((string) $this->type),
        };
    }
}

