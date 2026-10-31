<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'unit',
        'min_threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_threshold' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
