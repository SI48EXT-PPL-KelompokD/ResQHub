<?php

namespace App\Notifications;

use App\Models\MedicalReferral;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * AC 1: Notifikasi ke koordinator posko saat rujukan medis baru diajukan oleh nakes.
 */
class MedicalReferralCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public MedicalReferral $referral) {}

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
        $referral = $this->referral->loadMissing(['healthCheck.refugee.shelter', 'creator']);
        $check = $referral->healthCheck;
        $refugee = $check?->refugee;
        $shelter = $refugee?->shelter;

        $transportSuffix = $referral->transport_needed ? ' (membutuhkan transportasi)' : '';

        return [
            'type' => 'medical_referral_created',
            'medical_referral_id' => $referral->id,
            'health_check_id' => $referral->health_check_id,
            'refugee_id' => $refugee?->id,
            'refugee_name' => $refugee?->head_name,
            'shelter_id' => $shelter?->id,
            'shelter_name' => $shelter?->name,
            'facility_name' => $referral->facility_name,
            'transport_needed' => $referral->transport_needed,
            'triage' => $check?->triage,
            'status' => $referral->status,
            'created_by_name' => $referral->creator?->name,
            'message' => sprintf(
                'Rujukan medis diajukan: %s di %s dirujuk ke %s%s.',
                $refugee?->head_name ?? 'Pengungsi',
                $shelter?->name ?? 'posko',
                $referral->facility_name,
                $transportSuffix
            ),
        ];
    }
}
