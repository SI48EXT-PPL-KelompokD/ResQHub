<?php

namespace App\Models;

use Database\Factories\AidNeedFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AidNeed extends Model
{
    /** @use HasFactory<AidNeedFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'aid_needs';

    protected $fillable = [
        'shelter_id',
        'item_id',
        'qty_needed',
        'qty_pledged',
        'qty_fulfilled',
        'priority',
        'deadline',
        'status',
        'close_reason',
    ];

    protected function casts(): array
    {
        return [
            'qty_needed' => 'integer',
            'qty_pledged' => 'integer',
            'qty_fulfilled' => 'integer',
            'deadline' => 'date',
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

    public function remainingQuantity(): int
    {
        return max($this->qty_needed - $this->qty_fulfilled, 0);
    }
}
