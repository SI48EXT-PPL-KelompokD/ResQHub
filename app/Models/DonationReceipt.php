<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DonationReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'qty_good' => 'integer',
            'qty_damaged' => 'integer',
        ];
    }

    public function shelter(): BelongsTo
    {
        return $this->belongsTo(Shelter::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function goodsDonation(): BelongsTo
    {
        return $this->belongsTo(GoodsDonation::class, 'goods_donation_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'donation_receipt_id');
    }

    public function isCancelled(): bool
    {
        return !is_null($this->cancel_reason) || !is_null($this->deleted_at);
    }

    public function canBeModifiedOrCancelled(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        if ($this->goodsDonation && $this->goodsDonation->status === 'disalurkan') {
            return false;
        }

        $itemId = $this->goodsDonation?->aidNeed?->item_id;
        if (!$itemId) {
            return true;
        }

        $stock = Stock::where('shelter_id', $this->shelter_id)
            ->where('item_id', $itemId)
            ->first();

        if ($stock && $stock->quantity < $this->qty_good) {
            return false;
        }

        return true;
    }
}
