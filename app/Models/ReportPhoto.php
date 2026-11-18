<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * URL foto mengikuti alamat yang sedang dibuka (bukan APP_URL),
     * sama seperti foto fasilitas posko, agar tetap tampil saat diakses dari HP atau tunnel.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/'.ltrim($this->path, '/'));
    }
}
