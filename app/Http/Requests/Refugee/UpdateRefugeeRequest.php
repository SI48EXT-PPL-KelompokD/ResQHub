<?php

namespace App\Http\Requests\Refugee;

use App\Models\Refugee;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRefugeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();
        if (!$user) {
            return false;
        }

        /** @var Refugee|int|string|null $refugee */
        $refugee = $this->route('refugee');
        if (!$refugee instanceof Refugee) {
            $refugee = Refugee::find($refugee);
        }

        if (!$refugee) {
            return false;
        }

        return $user->canManageShelter((int) $refugee->shelter_id);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'head_name' => ['required', 'string', 'max:100'],
            'nik' => ['nullable', 'string', 'digits:16'],
            'members' => ['required', 'integer', 'min:1'],
            'infants' => ['nullable', 'integer', 'min:0'],
            'elderly' => ['nullable', 'integer', 'min:0'],
            'pregnant' => ['nullable', 'integer', 'min:0'],
            'disabled' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'head_name.required' => 'Nama kepala keluarga wajib diisi.',
            'head_name.max' => 'Nama kepala keluarga maksimal 100 karakter.',
            'nik.digits' => 'NIK harus terdiri dari tepat 16 digit angka.',
            'members.required' => 'Jumlah anggota keluarga wajib diisi.',
            'members.min' => 'Jumlah anggota keluarga minimal 1 orang.',
        ];
    }
}

