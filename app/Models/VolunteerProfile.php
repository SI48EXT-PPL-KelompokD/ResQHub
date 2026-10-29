<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VolunteerProfile extends Model
{
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
}
