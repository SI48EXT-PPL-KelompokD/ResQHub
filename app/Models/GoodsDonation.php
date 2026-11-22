<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GoodsDonation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'est_arrival' => 'date',
            'quantity' => 'integer',
        ];
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function aidNeed(): BelongsTo
    {
        return $this->belongsTo(AidNeed::class, 'aid_need_id');
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(DonationReceipt::class, 'goods_donation_id')->withTrashed();
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(DonationReceipt::class, 'goods_donation_id')->withTrashed();
    }

    public function isDijanjikan(): bool
    {
        return $this->status === 'dijanjikan';
    }

    public function isDiterima(): bool
    {
        return $this->status === 'diterima';
    }

    public function isDisalurkan(): bool
    {
        return $this->status === 'disalurkan';
    }

    public function isDibatalkan(): bool
    {
        return $this->status === 'dibatalkan';
    }
}

