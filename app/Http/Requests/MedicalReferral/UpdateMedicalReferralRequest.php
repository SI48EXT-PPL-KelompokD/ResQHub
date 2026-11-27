<?php

namespace App\Http\Requests\MedicalReferral;

use App\Models\MedicalReferral;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicalReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();
        $referral = $this->route('medicalReferral');

        if ($user === null || ! ($referral instanceof MedicalReferral)) {
            return false;
        }

        if ($user->isNakes()) {
            return true;
        }

        if ($user->isKoordinator()) {
            $shelterId = (int) $referral->healthCheck?->refugee?->shelter_id;

            return $shelterId > 0 && $user->canManageShelter($shelterId);
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return self::updateRules();
    }

    /**
     * Aturan validasi pembaruan status dan catatan perkembangan rujukan (dipakai ulang oleh Livewire).
     *
     * @return array<string, mixed>
     */
    public static function updateRules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(MedicalReferral::UPDATABLE_STATUSES)],
            'progress_note' => ['nullable', 'string', 'max:1000'],
            'outcome' => [
                'nullable',
                'required_if:status,'.MedicalReferral::STATUS_SELESAI,
                Rule::in(MedicalReferral::OUTCOMES),
            ],
            'facility_name' => ['sometimes', 'string', 'max:150'],
            'reason' => ['sometimes', 'string', 'max:1000'],
            'patient_condition' => ['sometimes', 'string', 'max:1000'],
            'transport_needed' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function updateMessages(): array
    {
        return [
            'status.required' => 'Status rujukan wajib dipilih.',
            'status.in' => 'Status rujukan tidak valid.',
            'progress_note.max' => 'Catatan perkembangan maksimal 1000 karakter.',
            'outcome.required_if' => 'Hasil akhir wajib dipilih ketika status rujukan Selesai.',
            'outcome.in' => 'Hasil akhir harus berupa Kembali ke posko atau Dirawat di faskes.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return self::updateMessages();
    }
}
