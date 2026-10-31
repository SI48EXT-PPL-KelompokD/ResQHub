<?php

namespace App\Http\Requests\HealthCheck;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreHealthCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        return $user !== null && $user->isNakes();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge([
            'refugee_id' => ['required', 'integer', 'exists:refugees,id'],
        ], self::vitalRules());
    }

    /**
     * Aturan validasi keluhan, tanda vital, catatan, dan triase (dipakai ulang oleh Livewire & update).
     *
     * @return array<string, mixed>
     */
    public static function vitalRules(): array
    {
        return [
            'complaint' => ['required', 'string', 'max:1000'],
            'temperature' => ['required', 'numeric', 'between:30,45'],
            'blood_pressure' => ['required', 'string', 'regex:/^\s*\d{2,3}\s*\/\s*\d{2,3}\s*$/'],
            'pulse' => ['required', 'integer', 'between:20,250'],
            'triage' => ['required', 'in:hijau,kuning,merah'],
            'action' => ['nullable', 'string', 'max:1000'],
            'checked_at' => ['nullable', 'date', 'before_or_equal:now'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function vitalMessages(): array
    {
        return [
            'refugee_id.required' => 'Pengungsi yang diperiksa wajib dipilih.',
            'refugee_id.exists' => 'Pengungsi tidak ditemukan / belum terdaftar.',
            'complaint.required' => 'Keluhan wajib diisi.',
            'complaint.max' => 'Keluhan maksimal 1000 karakter.',
            'temperature.required' => 'Suhu tubuh wajib diisi.',
            'temperature.numeric' => 'Suhu tubuh harus berupa angka.',
            'temperature.between' => 'Suhu tubuh harus di antara 30 - 45 °C.',
            'blood_pressure.required' => 'Tekanan darah wajib diisi.',
            'blood_pressure.regex' => 'Format tekanan darah harus sistolik/diastolik, contoh 120/80.',
            'pulse.required' => 'Nadi wajib diisi.',
            'pulse.integer' => 'Nadi harus berupa bilangan bulat.',
            'pulse.between' => 'Nadi harus di antara 20 - 250 kali/menit.',
            'triage.required' => 'Kategori triase wajib dipilih.',
            'triage.in' => 'Kategori triase harus Hijau, Kuning, atau Merah.',
            'action.max' => 'Catatan tindakan maksimal 1000 karakter.',
            'checked_at.before_or_equal' => 'Waktu pemeriksaan tidak boleh melebihi waktu saat ini.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return self::vitalMessages();
    }
}

