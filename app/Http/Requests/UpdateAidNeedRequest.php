<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAidNeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isKoordinator() ?? false;
    }

    public function rules(): array
    {
        return [
            'qty_needed' => ['required', 'integer', 'min:1'],
            'priority' => ['required', Rule::in(['mendesak', 'tinggi', 'normal'])],
            'deadline' => [
                'nullable',
                'date',
                'after_or_equal:today',
                'required_if:priority,mendesak',
            ],
        ];
    }

    protected function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $aidNeed = $this->route('aid_need');
            $minimum = ($aidNeed?->qty_pledged ?? 0) + ($aidNeed?->qty_fulfilled ?? 0);

            if ($this->integer('qty_needed') < $minimum) {
                $validator->errors()->add(
                    'qty_needed',
                    "Jumlah tidak boleh kurang dari {$minimum} yang sudah dijanjikan atau terpenuhi."
                );
            }
        });
    }
}
