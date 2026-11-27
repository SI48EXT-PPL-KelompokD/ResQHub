<?php

namespace App\Http\Requests\MedicalReferral;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        return $user !== null && $user->isNakes();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return self::referralRules();
    }

    /**
     * Aturan validasi pembuatan rujukan medis (dipakai ulang oleh Livewire).
     *
     * @return array<string, mixed>
     */
    public static function referralRules(): array
    {
        return [
            'health_check_id' => ['required', 'integer', 'exists:health_checks,id'],
            'facility_name' => ['required', 'string', 'max:150'],
            'reason' => ['required', 'string', 'max:1000'],
            'patient_condition' => ['required', 'string', 'max:1000'],
            'transport_needed' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function referralMessages(): array
    {
        return [
            'health_check_id.required' => 'Pemeriksaan asal wajib dipilih.',
            'health_check_id.exists' => 'Catatan pemeriksaan asal tidak ditemukan.',
            'facility_name.required' => 'Fasilitas kesehatan tujuan wajib diisi.',
            'facility_name.max' => 'Nama fasilitas kesehatan maksimal 150 karakter.',
            'reason.required' => 'Alasan rujukan wajib diisi.',
            'reason.max' => 'Alasan rujukan maksimal 1000 karakter.',
            'patient_condition.required' => 'Kondisi pasien saat dirujuk wajib diisi.',
            'patient_condition.max' => 'Kondisi pasien maksimal 1000 karakter.',
            'transport_needed.required' => 'Kebutuhan transportasi wajib ditentukan.',
            'transport_needed.boolean' => 'Kebutuhan transportasi harus bernilai ya atau tidak.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return self::referralMessages();
    }
}
