<?php

namespace App\Http\Requests\EarlyWarning;

use App\Models\EarlyWarning;

class UpdateEarlyWarningRequest extends StoreEarlyWarningRequest
{
    public function authorize(): bool
    {
        $warning = $this->route('warning');

        return $warning instanceof EarlyWarning && (bool) $this->user()?->can('update', $warning);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['publish']);

        return $rules + [
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
