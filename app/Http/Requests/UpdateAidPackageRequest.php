<?php

namespace App\Http\Requests;

use App\Models\AidPackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAidPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('aid_package')) ?? false;
    }

    public function rules(): array
    {
        /** @var AidPackage $package */
        $package = $this->route('aid_package');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('aid_packages', 'name')->ignore($package?->id)],
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

    protected function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var AidPackage|null $package */
            $package = $this->route('aid_package');

            if (! $package?->isUsed() || $validator->errors()->isNotEmpty()) {
                return;
            }

            $submitted = collect($this->input('items', []))
                ->mapWithKeys(fn (array $item) => [(int) ($item['item_id'] ?? 0) => (int) ($item['quantity'] ?? 0)])
                ->sortKeys()
                ->all();
            $current = $package->packageItems()
                ->pluck('quantity', 'item_id')
                ->map(fn ($quantity) => (int) $quantity)
                ->sortKeys()
                ->all();

            if ($submitted !== $current) {
                $validator->errors()->add(
                    'items',
                    'Komposisi paket terkunci karena paket ini sudah pernah disalurkan. Buat paket baru untuk komposisi berbeda.'
                );
            }
        });
    }
}
