<?php

namespace App\Http\Requests\EarlyWarning;

use App\Models\EarlyWarning;
use Illuminate\Foundation\Http\FormRequest;

class RevokeEarlyWarningRequest extends FormRequest
{
    public function authorize(): bool
    {
        $warning = $this->route('warning');

        return $warning instanceof EarlyWarning && (bool) $this->user()?->can('revoke', $warning);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'revoke_reason' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'revoke_reason.required' => 'Alasan pencabutan wajib diisi.',
            'revoke_reason.max' => 'Alasan pencabutan maksimal 255 karakter.',
        ];
    }
}
