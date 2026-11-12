<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AidNeed extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shelter_id', 'item_id', 'qty_needed', 'qty_pledged', 'qty_fulfilled',
        'priority', 'deadline', 'status', 'close_reason',
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

    public function goodsDonations(): HasMany
    {
        return $this->hasMany(GoodsDonation::class);
    }
}
