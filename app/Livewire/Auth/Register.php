<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\VolunteerProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Register extends Component
{
    // General fields
    public string $role = 'relawan';
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $regency = 'Kabupaten Bandung';
    public string $password = '';
    public string $password_confirmation = '';

    // Relawan specific fields
    public array $skills = [];
    public int $radius_km = 10;
    public string $availability = 'siap';

    // Nakes specific fields
    public string $str_number = '';
    public string $medical_facility = '';
    public string $medical_specialty = 'Perawat Lapangan';

    // Donatur specific fields
    public string $donor_type = 'individu';
    public string $organization_name = '';

    // Koordinator specific fields
    public string $coordinator_org = 'BPBD Kabupaten/Kota';

    // Warga specific fields
    public string $address = '';
    public ?int $family_members_count = null;

    // Available skills list from DB
    public array $availableSkills = [];

    protected function rules(): array
    {
        $rules = [
            'role' => 'required|in:admin,koordinator,nakes,relawan,donatur,warga',
            'name' => 'required|string|min:3|max:100',
            'email' => 'required|email|max:100|unique:users,email',
            'phone' => 'required|string|min:10|max:20|unique:users,phone',
            'regency' => 'required|string|max:100',
            'password' => 'required|string|min:8|confirmed',
        ];

        if ($this->role === 'relawan') {
            $rules['skills'] = 'required|array|min:1';
            $rules['radius_km'] = 'required|integer|min:1|max:100';
            $rules['availability'] = 'required|in:siap,tidak_siap';
        } elseif ($this->role === 'nakes') {
            $rules['str_number'] = 'required|string|min:6|max:30';
        }

        return $rules;
    }

    protected $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email ini sudah terdaftar sebelumnya.',
        'phone.required' => 'Nomor telepon/WhatsApp wajib diisi.',
        'phone.unique' => 'Nomor telepon ini sudah digunakan.',
        'password.required' => 'Kata sandi wajib diisi.',
        'password.min' => 'Kata sandi minimal 8 karakter.',
        'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        'skills.required' => 'Pilih minimal satu keahlian relawan.',
        'skills.min' => 'Pilih minimal satu keahlian relawan.',
        'str_number.required' => 'Nomor STR (Surat Tanda Registrasi) wajib diisi untuk Tenaga Medis.',
    ];

    public function mount(): void
    {
        $this->availableSkills = DB::table('skills')->select('id', 'name')->get()->toArray();
    }

    public function selectRole(string $newRole): void
    {
        $this->role = $newRole;
        $this->resetValidation();
    }

    public function register(): void
    {
        $this->validate();

        DB::transaction(function () {
            $user = User::create([
                'name' => $this->name,
                'email' => strtolower($this->email),
                'phone' => $this->phone,
                'password' => Hash::make($this->password),
                'role' => $this->role,
                'regency' => $this->regency,
                'is_active' => true,
            ]);

            if ($this->role === 'relawan') {
                $profile = VolunteerProfile::create([
                    'user_id' => $user->id,
                    'radius_km' => $this->radius_km,
                    'availability' => $this->availability,
                ]);

                if (!empty($this->skills)) {
                    $now = now();
                    $insertData = array_map(fn($skillId) => [
                        'volunteer_profile_id' => $profile->id,
                        'skill_id' => (int) $skillId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], $this->skills);

                    DB::table('volunteer_skill')->insert($insertData);
                }
            } elseif ($this->role === 'nakes') {
                $profile = VolunteerProfile::create([
                    'user_id' => $user->id,
                    'str_number' => $this->str_number,
                    'radius_km' => 15,
                    'availability' => 'siap',
                ]);

                $medisSkill = DB::table('skills')->where('name', 'like', '%Medis%')->first();
                if ($medisSkill) {
                    DB::table('volunteer_skill')->insert([
                        'volunteer_profile_id' => $profile->id,
                        'skill_id' => $medisSkill->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            Auth::login($user);
        });

        session()->flash('success', 'Pendaftaran berhasil! Selamat datang di ResQHub.');
        $this->redirect(route('admin.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('layouts.auth', ['title' => 'Daftar Akun Baru']);
    }
}
