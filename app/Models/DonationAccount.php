<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonationAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'disaster_event_id',
        'bank_name',
        'account_number',
        'account_holder',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Kejadian bencana pemilik rekening ini.
     */
    public function disasterEvent(): BelongsTo
    {
        return $this->belongsTo(DisasterEvent::class);
    }

    /**
     * Donasi dana yang masuk ke rekening ini.
     */
    public function fundDonations(): HasMany
    {
        return $this->hasMany(FundDonation::class);
    }

    /**
     * Scope untuk rekening yang berstatus aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Rekening yang sudah pernah menerima donasi tidak boleh dihapus.
     */
    public function hasDonations(): bool
    {
        return $this->fundDonations()->exists();
    }
}