<?php

namespace App\Http\Requests\HealthCheck;

use App\Models\HealthCheck;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class DeleteHealthCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();
        $check = $this->route('healthCheck');

        return $user !== null
            && $user->isNakes()
            && $check instanceof HealthCheck
            && $check->isCreatedBy($user);
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
            'delete_reason.required' => 'Alasan penghapusan catatan pemeriksaan wajib diisi.',
            'delete_reason.max' => 'Alasan penghapusan maksimal 255 karakter.',
        ];
    }
}

