<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskAssignment extends Model
{
    protected $fillable = [
        'volunteer_task_id',
        'volunteer_profile_id',
        'status',
        'reject_reason',
    ];

    public function volunteerTask(): BelongsTo
    {
        return $this->belongsTo(VolunteerTask::class);
    }

    public function volunteerProfile(): BelongsTo
    {
        return $this->belongsTo(VolunteerProfile::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(TaskReport::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['ditawarkan', 'diterima']);
    }
}
