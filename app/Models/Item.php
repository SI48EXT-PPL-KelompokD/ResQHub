<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'min_threshold' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function aidNeeds(): HasMany
    {
        return $this->hasMany(AidNeed::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'pangan' => 'Pangan',
            'sandang' => 'Sandang',
            'kebersihan' => 'Kebersihan',
            'obat_obatan' => 'Obat-obatan',
            'perlengkapan_bayi' => 'Perlengkapan Bayi',
            'peralatan' => 'Peralatan',
            default => ucfirst(str_replace('_', ' ', $this->category ?? '')),
        };
    }
}

