<?php

namespace App\Http\Requests\DisasterReport;

use App\Models\DisasterReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyDisasterReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $report = $this->route('report');

        return $report instanceof DisasterReport && (bool) $this->user()?->can('verify', $report);
    }

    /**
     * Terima: kaitkan ke kejadian yang ada (disaster_event_id) atau buat kejadian baru (new_event).
     * Tolak: alasan wajib diisi.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['terima', 'tolak'])],
            'reject_reason' => ['required_if:decision,tolak', 'nullable', 'string', 'max:1000'],
            'disaster_event_id' => ['nullable', 'integer', 'exists:disaster_events,id'],
            'new_event' => ['nullable', 'array'],
            'new_event.name' => [
                Rule::requiredIf(fn () => $this->input('decision') === 'terima' && ! $this->filled('disaster_event_id')),
                'nullable',
                'string',
                'max:150',
            ],
            'new_event.severity' => ['required_with:new_event.name', 'nullable', Rule::in(['ringan', 'sedang', 'berat'])],
            'new_event.regency' => ['required_with:new_event.name', 'nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan verifikasi wajib dipilih.',
            'reject_reason.required_if' => 'Alasan penolakan wajib diisi.',
            'disaster_event_id.exists' => 'Kejadian bencana tidak ditemukan.',
            'new_event.name.required' => 'Pilih kejadian yang ada atau isi nama kejadian baru.',
            'new_event.severity.required_with' => 'Tingkat keparahan kejadian baru wajib dipilih.',
            'new_event.regency.required_with' => 'Kabupaten/kota kejadian baru wajib diisi.',
        ];
    }
}
