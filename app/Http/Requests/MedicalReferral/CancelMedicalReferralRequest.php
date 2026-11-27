<?php

namespace App\Http\Requests\MedicalReferral;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class CancelMedicalReferralRequest extends FormRequest
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
        return self::cancelRules();
    }

    /**
     * @return array<string, mixed>
     */
    public static function cancelRules(): array
    {
        return [
            'cancel_reason' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function cancelMessages(): array
    {
        return [
            'cancel_reason.required' => 'Alasan pembatalan rujukan medis wajib diisi.',
            'cancel_reason.max' => 'Alasan pembatalan maksimal 255 karakter.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return self::cancelMessages();
    }
}
