<?php

namespace App\Http\Requests\Refugee;

use App\Models\Refugee;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class DeleteRefugeeRequest extends FormRequest
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
            'delete_reason' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'delete_reason.required' => 'Alasan penghapusan data pengungsi wajib diisi.',
            'delete_reason.max' => 'Alasan penghapusan maksimal 255 karakter.',
        ];
    }
}

