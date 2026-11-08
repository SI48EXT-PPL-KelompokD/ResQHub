<?php

namespace App\Http\Requests\EarlyWarning;

use App\Models\DisasterReport;
use App\Models\EarlyWarning;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEarlyWarningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', EarlyWarning::class);
    }

    /**
     * Wilayah sasaran: daftar kabupaten/kota, atau titik pusat dengan radius di peta.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'hazard_type' => ['required', Rule::in(DisasterReport::DISASTER_TYPES)],
            'level' => ['required', Rule::in(EarlyWarning::LEVELS)],
            'target_regencies' => ['required_without:radius_km', 'nullable', 'array', 'min:1'],
            'target_regencies.*' => ['string', 'max:100'],
            'center_lat' => ['required_with:radius_km', 'nullable', 'numeric', 'between:-90,90'],
            'center_lng' => ['required_with:radius_km', 'nullable', 'numeric', 'between:-180,180'],
            'radius_km' => ['required_without:target_regencies', 'nullable', 'integer', 'min:1', 'max:500'],
            'instruction' => ['required', 'string', 'max:2000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'disaster_event_id' => ['nullable', 'integer', 'exists:disaster_events,id'],
            'publish' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul peringatan wajib diisi.',
            'hazard_type.required' => 'Jenis ancaman wajib dipilih.',
            'level.required' => 'Tingkat peringatan wajib dipilih.',
            'level.in' => 'Tingkat peringatan harus Waspada, Siaga, atau Awas.',
            'target_regencies.required_without' => 'Pilih kabupaten/kota sasaran atau tentukan radius di peta.',
            'radius_km.required_without' => 'Pilih kabupaten/kota sasaran atau tentukan radius di peta.',
            'center_lat.required_with' => 'Titik pusat wilayah sasaran wajib ditentukan di peta.',
            'center_lng.required_with' => 'Titik pusat wilayah sasaran wajib ditentukan di peta.',
            'instruction.required' => 'Instruksi untuk warga wajib diisi.',
            'starts_at.required' => 'Waktu mulai berlaku wajib diisi.',
            'ends_at.required' => 'Masa berlaku wajib diisi.',
            'ends_at.after' => 'Masa berlaku harus berakhir setelah waktu mulai.',
            'disaster_event_id.exists' => 'Kejadian bencana tidak ditemukan.',
        ];
    }
}
