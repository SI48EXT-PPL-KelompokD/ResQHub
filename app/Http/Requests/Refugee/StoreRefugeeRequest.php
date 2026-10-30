<?php

namespace App\Http\Requests\Refugee;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreRefugeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();
        if (!$user) {
            return false;
        }

        $shelterId = (int) $this->input('shelter_id');
        if ($shelterId <= 0) {
            return $user->isAdmin() || $user->isKoordinator();
        }

        return $user->canManageShelter($shelterId);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'shelter_id' => ['required', 'integer', 'exists:shelters,id'],
            'head_name' => ['required', 'string', 'max:100'],
            'nik' => ['nullable', 'string', 'digits:16'],
            'members' => ['required', 'integer', 'min:1'],
            'infants' => ['nullable', 'integer', 'min:0'],
            'elderly' => ['nullable', 'integer', 'min:0'],
            'pregnant' => ['nullable', 'integer', 'min:0'],
            'disabled' => ['nullable', 'integer', 'min:0'],
            'checked_in_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'shelter_id.required' => 'Posko evakuasi wajib dipilih.',
            'shelter_id.exists' => 'Posko evakuasi tidak ditemukan.',
            'head_name.required' => 'Nama kepala keluarga wajib diisi.',
            'head_name.max' => 'Nama kepala keluarga maksimal 100 karakter.',
            'nik.digits' => 'NIK harus terdiri dari tepat 16 digit angka.',
            'members.required' => 'Jumlah anggota keluarga wajib diisi.',
            'members.min' => 'Jumlah anggota keluarga minimal 1 orang.',
            'infants.min' => 'Jumlah balita tidak boleh negatif.',
            'elderly.min' => 'Jumlah lansia tidak boleh negatif.',
            'pregnant.min' => 'Jumlah ibu hamil/menyusui tidak boleh negatif.',
            'disabled.min' => 'Jumlah penyandang disabilitas tidak boleh negatif.',
        ];
    }
}

