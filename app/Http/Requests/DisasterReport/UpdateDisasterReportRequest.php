<?php

namespace App\Http\Requests\DisasterReport;

use App\Models\DisasterReport;

class UpdateDisasterReportRequest extends StoreDisasterReportRequest
{
    public function authorize(): bool
    {
        $report = $this->route('report');

        return $report instanceof DisasterReport && (bool) $this->user()?->can('update', $report);
    }

    /**
     * Foto lama dapat dihapus lewat removed_photo_ids; total foto tetap dibatasi di service.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return parent::rules() + [
            'removed_photo_ids' => ['nullable', 'array'],
            'removed_photo_ids.*' => ['integer'],
        ];
    }
}
