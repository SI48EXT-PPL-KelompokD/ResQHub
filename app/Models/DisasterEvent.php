<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisasterEvent extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function shelters(): HasMany
    {
        return $this->hasMany(Shelter::class);
    }
}

