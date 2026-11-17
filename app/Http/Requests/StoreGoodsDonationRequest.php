<?php

namespace App\Http\Requests;

use App\Enums\DeliveryMethod;
use App\Models\AidNeed;
use App\Models\GoodsDonation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreGoodsDonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', GoodsDonation::class);
    }

    public function rules(): array
    {
        return [
            'aid_need_id' => [
                'required', 'integer',
                Rule::exists('aid_needs', 'id')->whereNull('deleted_at')->where('status', 'terbuka'),
            ],
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

            $need = AidNeed::find($this->integer('aid_need_id'));
            $remaining = max(0, $need->qty_needed - $need->qty_pledged);

            if ($this->integer('quantity') > $remaining) {
                $validator->errors()->add('quantity', "Jumlah maksimal {$remaining} sesuai sisa kebutuhan.");
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'aid_need_id' => 'kebutuhan',
            'quantity' => 'jumlah',
            'delivery_method' => 'metode',
            'est_arrival' => 'perkiraan tanggal tiba',
        ];
    }

    public function messages(): array
    {
        return [
            'aid_need_id.exists' => 'Kebutuhan ini sudah tidak dibuka untuk donasi.',
            'est_arrival.after_or_equal' => 'Perkiraan tanggal tiba tidak boleh sebelum hari ini.',
        ];
    }
}
