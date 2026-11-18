<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class DisasterEvent extends Model
{
    use HasFactory;

    protected $table = 'disaster_events';

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

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Scope untuk kejadian bencana yang masih aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Relasi ke posko-posko evakuasi di bawah kejadian bencana ini.
     */
    public function shelters(): HasMany
    {
        return $this->hasMany(Shelter::class);
    }

    /**
     * Relasi ke laporan kejadian warga yang ditautkan ke kejadian bencana ini.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(DisasterReport::class);
    }

    /**
     * Relasi ke peringatan dini yang terkait kejadian bencana ini.
     */
    public function earlyWarnings(): HasMany
    {
        return $this->hasMany(EarlyWarning::class);
    }
}
