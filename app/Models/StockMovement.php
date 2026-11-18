<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }

    public function donationReceipt(): BelongsTo
    {
        return $this->belongsTo(DonationReceipt::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'masuk' => 'Masuk',
            'keluar' => 'Keluar',
            'penyesuaian' => 'Penyesuaian',
            default => ucfirst($this->type),
        };
    }

    public function getFormattedQuantityAttribute(): string
    {
        if ($this->type === 'masuk') {
            return '+' . $this->quantity;
        }

        if ($this->type === 'keluar') {
            return '-' . abs($this->quantity);
        }

        // penyesuaian: can be positive or negative
        return ($this->quantity > 0 ? '+' : '') . $this->quantity;
    }
}
