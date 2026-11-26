<?php

namespace App\Models;

use Database\Factories\AidPackageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AidPackage extends Model
{
    /** @use HasFactory<AidPackageFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'target',
        'description',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function packageItems(): HasMany
    {
        return $this->hasMany(AidPackageItem::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'aid_package_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn (Builder $q) => $q->where('name', 'like', "%{$search}%"));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isUsed(): bool
    {
        return $this->distributions()->exists();
    }

    /**
     * @return array<int, array{item: Item, quantity: int, total: int}>
     */
    public function requirementsFor(int $targetCount): array
    {
        $targetCount = max(1, $targetCount);

        return $this->packageItems->map(fn (AidPackageItem $packageItem) => [
            'item' => $packageItem->item,
            'quantity' => (int) $packageItem->quantity,
            'total' => (int) $packageItem->quantity * $targetCount,
        ])->values()->all();
    }

    public function getTargetLabelAttribute(): string
    {
        return [
            'keluarga' => 'Keluarga',
            'jiwa' => 'Jiwa',
            'balita' => 'Balita',
            'lansia' => 'Lansia',
        ][$this->target] ?? ucfirst($this->target);
    }
}
