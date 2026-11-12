<?php

namespace App\Models;

use App\Enums\DeliveryMethod;
use App\Enums\GoodsDonationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsDonation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'donor_id', 'aid_need_id', 'quantity',
        'delivery_method', 'est_arrival', 'status', 'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'delivery_method' => DeliveryMethod::class,
            'status' => GoodsDonationStatus::class,
            'est_arrival' => 'date',
        ];
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function aidNeed(): BelongsTo
    {
        return $this->belongsTo(AidNeed::class);
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('donor_id', $user->id);
    }

    public function scopeWithStatus(Builder $query, GoodsDonationStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function isPledged(): bool
    {
        return $this->status === GoodsDonationStatus::Dijanjikan;
    }
}
