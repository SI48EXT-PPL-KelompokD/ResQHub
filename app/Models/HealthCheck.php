<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HealthCheck extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'refugee_id',
        'nakes_id',
        'complaint',
        'temperature',
        'blood_pressure',
        'pulse',
        'triage',
        'action',
        'checked_at',
        'delete_reason',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:1',
            'pulse' => 'integer',
            'checked_at' => 'datetime',
        ];
    }

    public function refugee(): BelongsTo
    {
        return $this->belongsTo(Refugee::class, 'refugee_id')->withTrashed();
    }

    public function nakes(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nakes_id');
    }
}

