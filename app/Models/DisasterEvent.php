<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisasterEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'disaster_type',
        'severity',
        'status',
        'regency',
        'lat',
        'lng',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(DisasterReport::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }
}
