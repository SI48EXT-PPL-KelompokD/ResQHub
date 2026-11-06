<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ReportPhoto extends Model
{
    protected $fillable = [
        'disaster_report_id',
        'path',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(DisasterReport::class, 'disaster_report_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
