<?php

namespace App\Http\Requests;

use App\Models\AidPackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAidPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', AidPackage::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', 'unique:aid_packages,name'],
            'target' => ['required', Rule::in(['keluarga', 'jiwa', 'balita', 'lansia'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('items', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
