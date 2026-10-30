<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $disaster_event_id
 * @property int $coordinator_id
 * @property string $name
 * @property string $address
 * @property string|float $lat
 * @property string|float $lng
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

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'capacity' => 'integer',
            'occupancy' => 'integer',
        ];
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function refugees(): HasMany
    {
        return $this->hasMany(Refugee::class, 'shelter_id');
    }

    public function activeRefugees(): HasMany
    {
        return $this->hasMany(Refugee::class, 'shelter_id')
            ->whereIn('status', ['di_posko', 'pindah']);
    }

    public function outgoingMovements(): HasMany
    {
        return $this->hasMany(RefugeeMovement::class, 'from_shelter_id');
    }

    public function incomingMovements(): HasMany
    {
        return $this->hasMany(RefugeeMovement::class, 'to_shelter_id');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class, 'shelter_id');
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
}

