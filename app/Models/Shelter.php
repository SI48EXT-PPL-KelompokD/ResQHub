<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shelter extends Model
{
    protected $table = 'shelters';

    public function aidNeeds(): HasMany
    {
        return $this->hasMany(AidNeed::class);
    }
}
