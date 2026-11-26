<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\VolunteerProfile;
use App\Support\IntendedRedirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Register extends Component
{
    /** Peran yang langsung aktif setelah mendaftar. */
    public const OPEN_ROLES = ['warga', 'relawan', 'donatur'];

    /** Peran petugas yang harus diaktifkan Admin Pusdalops (FR-00A). */
    public const APPROVAL_ROLES = ['nakes', 'koordinator'];

    public const REGENCIES = [
        'Kabupaten Bandung',
        'Kota Bandung',
        'Kabupaten Bandung Barat',
        'Kota Cimahi',
        'Kabupaten Sumedang',
        'Kabupaten Garut',
        'Kabupaten Cianjur',
    ];

    public int $step = 1;

    public string $role = '';

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $regency = 'Kabupaten Bandung';

    public string $password = '';

    public string $password_confirmation = '';

    // Khusus relawan
    public array $skills = [];

    public int $radius_km = 10;

    public string $availability = 'siap';

    // Khusus tenaga kesehatan
    public string $str_number = '';

    public array $availableSkills = [];

    public function mount(): void
    {
        $this->availableSkills = DB::table('skills')->orderBy('name')->get(['id', 'name'])->toArray();

        $requested = (string) request()->query('peran', '');

        if ($this->isAllowedRole($requested)) {
            $this->role = $requested;
            $this->step = 2;
        }
    }

    public function selectRole(string $role): void
    {
        if ($this->isAllowedRole($role)) {
            $this->role = $role;
            $this->resetValidation();
        }
    }

    public function continueToDetails(): void
    {
        if (! $this->isAllowedRole($this->role)) {
            $this->addError('role', 'Pilih salah satu peran untuk melanjutkan.');

            return;
        }

        $this->step = 2;
    }

    public function backToRole(): void
    {
        $this->resetValidation();
        $this->step = 1;
    }

    public function needsApproval(): bool
    {
        return in_array($this->role, self::APPROVAL_ROLES, true);
    }

    protected function rules(): array
    {
        $rules = [
            'role' => ['required', 'in:'.implode(',', [...self::OPEN_ROLES, ...self::APPROVAL_ROLES])],
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/', 'unique:users,phone'],
            'regency' => ['required', 'in:'.implode(',', self::REGENCIES)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if ($this->role === 'relawan') {
            $rules['skills'] = ['required', 'array', 'min:1'];
            $rules['skills.*'] = ['integer', 'exists:skills,id'];
            $rules['radius_km'] = ['required', 'integer', 'in:5,10,20,50'];
            $rules['availability'] = ['required', 'in:siap,tidak_siap'];
        }

        if ($this->role === 'nakes') {
            $rules['str_number'] = ['required', 'string', 'min:6', 'max:30'];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'role.required' => 'Pilih salah satu peran untuk melanjutkan.',
            'role.in' => 'Peran yang dipilih tidak tersedia untuk pendaftaran.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama lengkap minimal 3 karakter.',
            'name.max' => 'Nama lengkap maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 100 karakter.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.regex' => 'Gunakan nomor HP Indonesia yang valid, contoh 081234567890.',
            'phone.unique' => 'Nomor HP ini sudah terdaftar.',
            'regency.required' => 'Wilayah wajib dipilih.',
            'regency.in' => 'Wilayah yang dipilih tidak tersedia.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'skills.required' => 'Pilih minimal satu keahlian.',
            'skills.min' => 'Pilih minimal satu keahlian.',
            'skills.*.exists' => 'Keahlian yang dipilih tidak tersedia.',
            'radius_km.in' => 'Jangkauan tugas yang dipilih tidak tersedia.',
            'availability.in' => 'Status kesiapan yang dipilih tidak tersedia.',
            'str_number.required' => 'Nomor STR wajib diisi untuk tenaga kesehatan.',
            'str_number.min' => 'Nomor STR minimal 6 karakter.',
            'str_number.max' => 'Nomor STR maksimal 30 karakter.',
        ];
    }

    public function register(): void
    {
        $this->email = strtolower(trim($this->email));
        $this->phone = preg_replace('/[\s-]/', '', $this->phone);

        $this->validate();

        $needsApproval = $this->needsApproval();

        $user = DB::transaction(function () use ($needsApproval) {
            $user = User::create([
                'name' => trim($this->name),
                'email' => $this->email,
                'phone' => $this->phone,
                'password' => Hash::make($this->password),
                'role' => $this->role,
                'regency' => $this->regency,
                'is_active' => ! $needsApproval,
            ]);

            if ($this->role === 'relawan') {
                $profile = VolunteerProfile::create([
                    'user_id' => $user->id,
                    'radius_km' => $this->radius_km,
                    'availability' => $this->availability,
                ]);

                $profile->skills()->attach(array_map('intval', $this->skills));
            }

            if ($this->role === 'nakes') {
                $profile = VolunteerProfile::create([
                    'user_id' => $user->id,
                    'str_number' => $this->str_number,
                    'radius_km' => 15,
                    'availability' => 'siap',
                ]);

                $medicalSkillId = DB::table('skills')->where('name', 'like', '%Medis%')->value('id');

                if ($medicalSkillId) {
                    $profile->skills()->attach($medicalSkillId);
                }
            }

            return $user;
        });

        if ($needsApproval) {
            session()->flash('status', 'Pendaftaran terkirim. Anda dapat masuk setelah akun diaktifkan oleh Admin Pusdalops.');
            $this->redirect(route('login'));

            return;
        }

        Auth::login($user);
        session()->regenerate();
        IntendedRedirect::forgetIfNotAllowed($user);

        $this->redirectIntended(route('admin.dashboard'));
    }

    private function isAllowedRole(string $role): bool
    {
        return in_array($role, [...self::OPEN_ROLES, ...self::APPROVAL_ROLES], true);
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('layouts.auth', ['title' => 'Daftar Akun', 'width' => 'max-w-xl']);
    }
}
