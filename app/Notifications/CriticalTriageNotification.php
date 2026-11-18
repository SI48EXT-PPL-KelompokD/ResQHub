<?php

namespace App\Notifications;

use App\Models\HealthCheck;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * AC 1: Notifikasi ke koordinator posko saat pengungsi ditriase Merah (gawat).
 */
class CriticalTriageNotification extends Notification
{
    use Queueable;

    public function __construct(public HealthCheck $healthCheck) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $check = $this->healthCheck->loadMissing(['refugee.shelter', 'nakes']);
        $refugee = $check->refugee;
        $shelter = $refugee?->shelter;

        return [
            'type' => 'critical_triage',
            'health_check_id' => $check->id,
            'refugee_id' => $check->refugee_id,
            'refugee_name' => $refugee?->head_name,
            'shelter_id' => $shelter?->id,
            'shelter_name' => $shelter?->name,
            'nakes_name' => $check->nakes?->name,
            'complaint' => $check->complaint,
            'triage' => $check->triage,
            'checked_at' => $check->checked_at?->toIso8601String(),
            'message' => sprintf(
                'Triase MERAH: %s di %s perlu penanganan segera (%s).',
                $refugee?->head_name ?? 'Pengungsi',
                $shelter?->name ?? 'posko',
                str($check->complaint)->limit(80)
            ),
        ];
    }
}
