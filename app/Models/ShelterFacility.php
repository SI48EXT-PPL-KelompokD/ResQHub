<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShelterFacility extends Model
{
    use HasFactory;

    protected $table = 'shelter_facilities';

    /**
     * Daftar jenis fasilitas standar posko evakuasi sesuai PBI-05.
     */
    public const TYPES = [
        'mck' => 'MCK / Sanitasi',
        'dapur_umum' => 'Dapur Umum',
        'pos_kesehatan' => 'Pos Kesehatan',
        'listrik' => 'Listrik & Penerangan',
        'air_bersih' => 'Air Bersih',
        'laktasi' => 'Ruang Laktasi',
        'ramah_anak' => 'Area Ramah Anak',
    ];

    /**
     * Daftar ikon representatif untuk tiap jenis fasilitas.
     */
    public const TYPE_ICONS = [
        'mck' => '🚿',
        'dapur_umum' => '🍲',
        'pos_kesehatan' => '🏥',
        'listrik' => '⚡',
        'air_bersih' => '💧',
        'laktasi' => '🤱',
        'ramah_anak' => '🧸',
    ];

    /**
     * Daftar satuan layanan tiap jenis fasilitas untuk tampilan informatif.
     */
    public const TYPE_CAPACITY_UNITS = [
        'mck' => 'pengguna',
        'dapur_umum' => 'porsi/hari',
        'pos_kesehatan' => 'pasien',
        'listrik' => 'jiwa/titik',
        'air_bersih' => 'liter/hari',
        'laktasi' => 'ibu',
        'ramah_anak' => 'anak',
    ];

    /**
     * Daftar kondisi kelayakan fasilitas posko.
     */
    public const CONDITIONS = [
        'baik' => 'Baik',
        'rusak_ringan' => 'Rusak Ringan',
        'tidak_berfungsi' => 'Tidak Berfungsi',
    ];

    protected $fillable = [
        'shelter_id',
        'type',
        'units',
        'capacity',
        'condition',
        'note',
        'photo_path',
    ];

    protected $casts = [
        'units' => 'integer',
        'capacity' => 'integer',
    ];

    /**
     * Relasi balik ke posko evakuasi pemilik fasilitas ini.
     */
    public function shelter(): BelongsTo
    {
        return $this->belongsTo(Shelter::class, 'shelter_id');
    }

    /**
     * Label nama jenis fasilitas yang mudah dibaca.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', (string) $this->type));
    }

    /**
     * Ikon jenis fasilitas.
     */
    public function getTypeIconAttribute(): string
    {
        return self::TYPE_ICONS[$this->type] ?? '🛠️';
    }

    /**
     * Satuan kapasitas layanan fasilitas.
     */
    public function getCapacityUnitAttribute(): string
    {
        return self::TYPE_CAPACITY_UNITS[$this->type] ?? 'jiwa';
    }

    /**
     * Label status kondisi fasilitas.
     */
    public function getConditionLabelAttribute(): string
    {
        return self::CONDITIONS[$this->condition] ?? ucfirst(str_replace('_', ' ', (string) $this->condition));
    }

    /**
     * Kelas CSS badge warna kondisi fasilitas (Tailwind CSS).
     */
    public function getConditionBadgeClassesAttribute(): string
    {
        return match ($this->condition) {
            'tidak_berfungsi' => 'bg-red-50 text-red-700 border-red-200',
            'rusak_ringan' => 'bg-amber-50 text-amber-700 border-amber-200',
            default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        };
    }

    /**
     * Kelas warna titik indikator kondisi fasilitas.
     */
    public function getConditionDotClassAttribute(): string
    {
        return match ($this->condition) {
            'tidak_berfungsi' => 'bg-red-500',
            'rusak_ringan' => 'bg-amber-500',
            default => 'bg-emerald-500',
        };
    }

    /**
     * URL publik foto bukti kondisi fasilitas (jika tersedia).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        return asset('storage/'.ltrim($this->photo_path, '/'));
    }

    /**
     * Scope fasilitas yang berstatus "Tidak Berfungsi".
     */
    public function scopeBroken(Builder $query): Builder
    {
        return $query->where('condition', 'tidak_berfungsi');
    }

    /**
     * Scope fasilitas yang memerlukan penanganan (Rusak Ringan atau Tidak Berfungsi).
     */
    public function scopeNeedsAttention(Builder $query): Builder
    {
        return $query->whereIn('condition', ['rusak_ringan', 'tidak_berfungsi']);
    }
}
