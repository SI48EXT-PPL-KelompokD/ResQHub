<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'min_threshold' => 'integer',
        ];
    }

    public function shelter(): BelongsTo
    {
        return $this->belongsTo(Shelter::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest('id');
    }

    /**
     * Cek apakah stok berstatus menipis (di bawah ambang batas).
     */
    public function isDepleted(): bool
    {
        return $this->quantity < $this->min_threshold;
    }

    /**
     * Label status stok: Menipis, Habis, atau Aman.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'Habis';
        }

        if ($this->isDepleted()) {
            return 'Menipis';
        }

        return 'Aman';
    }

    /**
     * Hitung estimasi hari tersisa berdasarkan rata-rata penyaluran 3 hari terakhir.
     */
    public function getDaysRemainingEstimateAttribute(): array
    {
        $since = Carbon::now()->subDays(3);

        // Ambil penyaluran dari mutasi keluar (stock_movements type 'keluar')
        $movementOut = $this->movements()
            ->where('type', 'keluar')
            ->where('created_at', '>=', $since)
            ->sum('quantity');

        // Ambil juga dari distributions jika ada catatan langsung penyaluran
        $distOut = Distribution::where('shelter_id', $this->shelter_id)
            ->where('item_id', $this->item_id)
            ->whereNull('cancel_reason')
            ->where('distributed_at', '>=', $since)
            ->sum('quantity');

        // Total penyaluran 3 hari terakhir (hindari double count dengan mengambil nilai terbesar)
        $totalOut = max($movementOut, $distOut);
        $avgDaily = $totalOut / 3;

        if ($this->quantity <= 0) {
            return [
                'days' => 0,
                'avg_daily' => round($avgDaily, 1),
                'total_3d' => $totalOut,
                'label' => '0 hari (Habis)',
                'is_low' => true,
            ];
        }

        if ($avgDaily > 0) {
            $days = round($this->quantity / $avgDaily, 1);
            return [
                'days' => $days,
                'avg_daily' => round($avgDaily, 1),
                'total_3d' => $totalOut,
                'label' => $days . ' hari',
                'is_low' => $days <= 3,
            ];
        }

        return [
            'days' => null,
            'avg_daily' => 0,
            'total_3d' => 0,
            'label' => '– (Tidak ada penyaluran 3 hari)',
            'is_low' => false,
        ];
    }

    /**
     * Cek apakah terdapat kebutuhan terbuka di posko ini untuk barang ini.
     */
    public function hasOpenNeeds(): bool
    {
        return AidNeed::where('shelter_id', $this->shelter_id)
            ->where('item_id', $this->item_id)
            ->where('status', 'terbuka')
            ->exists();
    }

    /**
     * Cek apakah stok boleh dihapus:
     * Hanya boleh dihapus jika jumlah = 0 dan tidak memiliki kebutuhan terbuka.
     */
    public function canBeDeleted(): bool
    {
        return $this->quantity == 0 && ! $this->hasOpenNeeds();
    }
}

