<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('item')) ?? false;
    }

    public function rules(): array
    {
        /** @var Item $item */
        $item = $this->route('item');

        return [
            'name' => [
                'required', 'string', 'max:150',
                Rule::unique('items', 'name')
                    ->where(fn ($query) => $query->where('unit', $this->input('unit')))
                    ->ignore($item->id),
            ],
            'category' => ['required', Rule::in(['pangan', 'sandang', 'kebersihan', 'obat_obatan', 'perlengkapan_bayi', 'peralatan'])],
            'unit' => ['required', 'string', 'max:20'],
            'min_threshold' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var Item $item */
            $item = $this->route('item');

            if ($item && $item->unit !== $this->input('unit') && $item->stocks()->exists()) {
                $validator->errors()->add('unit', 'Satuan tidak dapat diubah karena barang ini sudah memiliki stok.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama barang dengan satuan tersebut sudah ada.',
        ];
    }
}
