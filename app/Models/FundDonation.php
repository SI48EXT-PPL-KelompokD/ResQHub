<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundDonation extends Model
{
    protected $fillable = [
        'code',
        'donor_id',
        'disaster_event_id',
        'donation_account_id',
        'amount',
        'is_anonymous',
        'proof_path',
        'status',
        'verified_by',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_anonymous' => 'boolean',
        ];
    }

    public function donationAccount(): BelongsTo
    {
        return $this->belongsTo(DonationAccount::class);
    }
}