<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VolunteerTask extends Model
{
    protected $table = 'volunteer_tasks';

    protected $fillable = [
        'disaster_event_id',
        'shelter_id',
        'sos_request_id',
        'skill_id',
        'title',
        'description',
        'lat',
        'lng',
        'quota',
        'start_at',
        'end_at',
        'status',
        'cancel_reason',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function scopeOngoing($query)
    {
        return $query->whereIn('status', ['terbuka', 'berjalan']);
    }
}
