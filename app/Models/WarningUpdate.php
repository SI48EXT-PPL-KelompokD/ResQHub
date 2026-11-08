<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarningUpdate extends Model
{
    protected $fillable = [
        'early_warning_id',
        'old_level',
        'new_level',
        'note',
        'updated_by',
    ];

    public function warning(): BelongsTo
    {
        return $this->belongsTo(EarlyWarning::class, 'early_warning_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
