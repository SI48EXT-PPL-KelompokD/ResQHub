<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'subject_type', 'subject_id', 'action', 'changes'];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(Model $subject, string $action, ?array $changes = null): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'action' => $action,
            'changes' => $changes,
        ]);
    }
}