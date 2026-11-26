<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AidPackageItem extends Model
{
    protected $table = 'aid_package_items';

    protected $fillable = ['aid_package_id', 'item_id', 'quantity'];

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    public function aidPackage(): BelongsTo
    {
        return $this->belongsTo(AidPackage::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
