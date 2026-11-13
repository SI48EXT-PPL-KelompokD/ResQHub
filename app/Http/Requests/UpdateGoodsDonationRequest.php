<?php

namespace App\Http\Requests;

use App\Enums\DeliveryMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateGoodsDonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('goodsDonation'));
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
            'delivery_method' => ['required', Rule::enum(DeliveryMethod::class)],
            'est_arrival' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $donation = $this->route('goodsDonation');
            $need = $donation->aidNeed;
            $remaining = max(0, $need->qty_needed - $need->qty_pledged + $donation->quantity);

            if ($this->integer('quantity') > $remaining) {
                $validator->errors()->add('quantity', "Jumlah maksimal {$remaining} sesuai sisa kebutuhan.");
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'quantity' => 'jumlah',
            'delivery_method' => 'metode',
            'est_arrival' => 'perkiraan tanggal tiba',
        ];
    }

    public function messages(): array
    {
        return [
            'est_arrival.after_or_equal' => 'Perkiraan tanggal tiba tidak boleh sebelum hari ini.',
        ];
    }
}
