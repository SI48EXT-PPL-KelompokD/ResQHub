<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'unit',
        'min_threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_threshold' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function aidNeeds(): HasMany { return $this->hasMany(AidNeed::class); }
    public function aidPackageItems(): HasMany { return $this->hasMany(AidPackageItem::class); }
    public function stocks(): HasMany { return $this->hasMany(Stock::class); }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn (Builder $q) => $q->where('name', 'like', "%{$search}%"));
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return $query->when($category, fn (Builder $q) => $q->where('category', $category));
    }
}
