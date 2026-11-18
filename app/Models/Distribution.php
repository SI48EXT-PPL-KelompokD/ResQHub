<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Distribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'shelter_id',
        'item_id',
        'aid_package_id',
        'refugee_id',
        'recipient_note',
        'quantity',
        'photo_path',
        'distributed_by',
        'distributed_at',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'distributed_at' => 'datetime',
        ];
    }

    public function shelter(): BelongsTo
    {
        return $this->belongsTo(Shelter::class, 'shelter_id');
    }

    public function refugee(): BelongsTo
    {
        return $this->belongsTo(Refugee::class, 'refugee_id')->withTrashed();
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }
}

