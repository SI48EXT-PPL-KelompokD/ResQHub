<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $health_check_id
 * @property string $facility_name
 * @property string $reason
 * @property string $patient_condition
 * @property bool $transport_needed
 * @property string $status
 * @property string|null $outcome
 * @property string|null $cancel_reason
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class MedicalReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'health_check_id',
        'facility_name',
        'reason',
        'patient_condition',
        'transport_needed',
        'status',
        'outcome',
        'cancel_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transport_needed' => 'boolean',
        ];
    }

    public function healthCheck(): BelongsTo
    {
        return $this->belongsTo(HealthCheck::class, 'health_check_id')->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

