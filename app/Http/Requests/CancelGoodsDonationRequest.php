<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelGoodsDonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('cancel', $this->route('goodsDonation'));
    }

    public function rules(): array
    {
        return [
            'cancel_reason' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return ['cancel_reason' => 'alasan pembatalan'];
    }
}
