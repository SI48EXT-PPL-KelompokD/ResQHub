<?php

namespace App\Http\Requests\DisasterReport;

use App\Models\DisasterReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisasterReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', DisasterReport::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'disaster_type' => ['required', Rule::in(DisasterReport::DISASTER_TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'estimated_affected' => ['nullable', 'integer', 'min:0'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['required', 'string', 'max:255'],
            'occurred_at' => ['required', 'date', 'before_or_equal:now'],
            'photos' => ['nullable', 'array', 'max:'.DisasterReport::MAX_PHOTOS],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'disaster_type.required' => 'Jenis bencana wajib dipilih.',
            'disaster_type.in' => 'Jenis bencana tidak valid.',
            'title.required' => 'Judul laporan wajib diisi.',
            'description.required' => 'Deskripsi kondisi wajib diisi.',
            'estimated_affected.min' => 'Perkiraan jumlah terdampak tidak boleh negatif.',
            'lat.required' => 'Titik lokasi di peta wajib ditentukan.',
            'lng.required' => 'Titik lokasi di peta wajib ditentukan.',
            'address.required' => 'Alamat lokasi wajib diisi.',
            'occurred_at.required' => 'Waktu kejadian wajib diisi.',
            'occurred_at.before_or_equal' => 'Waktu kejadian tidak boleh di masa depan.',
            'photos.max' => 'Maksimal '.DisasterReport::MAX_PHOTOS.' foto per laporan.',
            'photos.*.image' => 'File harus berupa gambar.',
            'photos.*.mimes' => 'Foto harus berformat JPG atau PNG.',
            'photos.*.max' => 'Ukuran setiap foto maksimal 2 MB.',
        ];
    }
}
