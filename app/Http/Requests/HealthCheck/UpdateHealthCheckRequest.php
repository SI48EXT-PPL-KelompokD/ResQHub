<?php

namespace App\Http\Requests\HealthCheck;

use App\Models\HealthCheck;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHealthCheckRequest extends FormRequest
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
        return StoreHealthCheckRequest::vitalRules();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return StoreHealthCheckRequest::vitalMessages();
    }
}

