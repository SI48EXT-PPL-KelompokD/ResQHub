<?php

namespace App\Http\Requests;

use App\Models\AidNeed;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAidNeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isKoordinator() ?? false;
    }

    public function rules(): array
    {
        return [
            'shelter_id' => [
                'required',
                'integer',
                Rule::exists('shelters', 'id')->where(
                    fn ($query) => $query
                        ->where('coordinator_id', $this->user()->id)
                        ->whereIn('status', ['aktif', 'penuh'])
                ),
            ],
            'item_id' => [
                'required',
                'integer',
                Rule::exists('items', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],
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
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $duplicate = AidNeed::query()
                ->where('shelter_id', $this->integer('shelter_id'))
                ->where('item_id', $this->integer('item_id'))
                ->where('status', 'terbuka')
                ->exists();

            if ($duplicate) {
                $validator->errors()->add(
                    'item_id',
                    'Barang tersebut masih memiliki kebutuhan terbuka di posko yang dipilih.'
                );
            }
        });
    }
}
