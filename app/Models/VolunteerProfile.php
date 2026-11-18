<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VolunteerProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'domicile_lat',
        'domicile_lng',
        'radius_km',
        'availability',
        'str_number',
        'certificate_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'volunteer_skill')->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function getHasMedicalSkillAttribute(): bool
    {
        return $this->skills->contains(
            fn (Skill $skill) => str_contains(strtolower($skill->name), 'medis')
        );
    }

    public function getIsCompleteAttribute(): bool
    {
        return filled($this->user?->regency) && $this->skills->isNotEmpty();
    }

    public function getReadinessLabelAttribute(): string
    {
        if (! $this->is_complete) {
            return 'Belum Lengkap';
        }

        return $this->availability === 'siap' ? 'Siap Ditugaskan' : 'Tidak Siap';
    }

    public function scopeReadyForRecommendation($query)
    {
        return $query->where('availability', 'siap');
    }
}
