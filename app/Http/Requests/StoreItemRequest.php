<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Item::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('items', 'name')->where(fn ($query) => $query->where('unit', $this->input('unit')))],
            'category' => ['required', Rule::in(['pangan', 'sandang', 'kebersihan', 'obat_obatan', 'perlengkapan_bayi', 'peralatan'])],
            'unit' => ['required', 'string', 'max:20'],
            'min_threshold' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama barang dengan satuan tersebut sudah ada.',
        ];
    }
}
