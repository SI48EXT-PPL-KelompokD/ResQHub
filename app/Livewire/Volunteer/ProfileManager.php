<?php

namespace App\Livewire\Volunteer;

use App\Models\Skill;
use App\Models\VolunteerProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileManager extends Component
{
    use WithFileUploads;

    public const REGENCIES = [
        'Kabupaten Bandung',
        'Kabupaten Bandung Barat',
        'Kota Bandung',
        'Kota Cimahi',
        'Kabupaten Sumedang',
        'Kabupaten Garut',
        'Kabupaten Subang',
        'Kabupaten Purwakarta',
        'Kabupaten Karawang',
        'Kabupaten Bekasi',
        'Kota Bekasi',
        'Kabupaten Bogor',
        'Kota Bogor',
        'Kota Depok',
        'Kabupaten Cianjur',
        'Kabupaten Sukabumi',
        'Kota Sukabumi',
        'Kabupaten Tasikmalaya',
        'Kota Tasikmalaya',
        'Kabupaten Ciamis',
        'Kabupaten Pangandaran',
        'Kabupaten Kuningan',
        'Kabupaten Majalengka',
        'Kabupaten Indramayu',
        'Kabupaten Cirebon',
        'Kota Cirebon',
    ];

    public string $mode = 'view';

    public $regency = '';

    public $radius_km = 10;

    public $availability = 'siap';

    public $str_number = null;

    public array $selectedSkills = [];

    public $certificate = null;

    public bool $removeCertificate = false;

    public bool $showDeleteModal = false;

    public bool $open = false;

    public ?string $notification = null;

    public string $notificationType = 'success';

    public function mount(): void
    {
        if (! auth()->user()?->canManageVolunteerProfile()) {
            abort(403, 'Akses Ditolak: Profil keahlian hanya untuk relawan, nakes, dan koordinator.');
        }

        $this->fillFromProfile();
    }

    #[On('open-profile')]
    public function openProfile(): void
    {
        $this->fillFromProfile();
        $this->resetValidation();
        $this->notification = null;
        $this->mode = 'view';
        $this->open = true;
    }

    public function closeProfile(): void
    {
        $this->open = false;
        $this->mode = 'view';
        $this->showDeleteModal = false;
    }

    public function profile(): ?VolunteerProfile
    {
        return auth()->user()->volunteerProfile()->with('skills')->first();
    }

    public function fillFromProfile(): void
    {
        $user = auth()->user();
        $profile = $this->profile();

        $this->regency = $user->regency ?? '';
        $this->radius_km = $profile->radius_km ?? 10;
        $this->availability = $profile->availability ?? 'siap';
        $this->str_number = $profile->str_number ?? null;
        $this->selectedSkills = $profile
            ? $profile->skills->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];
        $this->certificate = null;
        $this->removeCertificate = false;
    }

    public function edit(): void
    {
        $this->fillFromProfile();
        $this->notification = null;
        $this->resetValidation();
        $this->mode = 'edit';
    }

    public function cancel(): void
    {
        $this->fillFromProfile();
        $this->resetValidation();
        $this->mode = 'view';
    }

    public function updatedSelectedSkills(): void
    {
        if (! $this->hasMedicalSkill()) {
            $this->str_number = null;
            $this->resetValidation('str_number');
        }
    }

    public function hasMedicalSkill(): bool
    {
        $ids = array_map('intval', (array) $this->selectedSkills);

        if ($ids === []) {
            return false;
        }

        return Skill::whereIn('id', $ids)
            ->where('name', 'like', '%Medis%')
            ->exists();
    }

    public function save(): void
    {
        $rules = [
            'regency' => ['required', 'string', 'max:100'],
            'radius_km' => ['required', 'integer', 'min:1', 'max:200'],
            'availability' => ['required', Rule::in(['siap', 'tidak_siap'])],
            'selectedSkills' => ['required', 'array', 'min:1'],
            'selectedSkills.*' => ['integer', 'exists:skills,id'],
            'str_number' => ['nullable', 'string', 'max:30'],
            'certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];

        if ($this->hasMedicalSkill()) {
            $rules['str_number'] = ['required', 'string', 'max:30'];
        }

        $this->validate($rules, [
            'regency.required' => 'Kabupaten/Kota domisili wajib dipilih.',
            'radius_km.required' => 'Radius kesediaan wajib diisi.',
            'selectedSkills.required' => 'Pilih minimal satu keahlian.',
            'selectedSkills.min' => 'Pilih minimal satu keahlian.',
            'str_number.required' => 'Nomor STR wajib diisi untuk keahlian medis.',
            'certificate.mimes' => 'Sertifikat harus berupa PDF, JPG, JPEG, atau PNG.',
            'certificate.max' => 'Ukuran sertifikat maksimal 5 MB.',
        ]);

        $user = auth()->user();
        $user->update(['regency' => $this->regency]);

        $profile = VolunteerProfile::withTrashed()->firstOrNew(['user_id' => $user->id]);

        if ($profile->trashed()) {
            $profile->restore();
        }

        if ($this->certificate) {
            if ($profile->certificate_path) {
                Storage::disk('public')->delete($profile->certificate_path);
            }

            $profile->certificate_path = $this->certificate->store('certificates', 'public');
        } elseif ($this->removeCertificate && $profile->certificate_path) {
            Storage::disk('public')->delete($profile->certificate_path);
            $profile->certificate_path = null;
        }

        $profile->radius_km = (int) $this->radius_km;
        $profile->availability = $this->availability;
        $profile->str_number = $this->hasMedicalSkill() ? $this->str_number : null;
        $profile->save();

        $profile->skills()->sync(array_map('intval', $this->selectedSkills));

        $this->fillFromProfile();
        $this->mode = 'view';
        $this->notify('Profil keahlian berhasil disimpan.');
    }

    public function requestDelete(): void
    {
        if ($this->hasActiveTask()) {
            $this->showDeleteModal = false;
            $this->notify('Profil tidak dapat dihapus selama masih ada tugas berjalan.', 'error');

            return;
        }

        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteProfile(): void
    {
        if ($this->hasActiveTask()) {
            $this->showDeleteModal = false;
            $this->notify('Profil tidak dapat dihapus selama masih ada tugas berjalan.', 'error');

            return;
        }

        $profile = $this->profile();
        $profile?->delete();

        $this->showDeleteModal = false;
        $this->mode = 'view';
        $this->fillFromProfile();
        $this->notify('Profil relawan berhasil dihapus. Riwayat tugas tetap tersimpan.');
    }

    public function hasActiveTask(): bool
    {
        $profile = $this->profile();

        if (! $profile) {
            return false;
        }

        return DB::table('task_assignments')
            ->join('volunteer_tasks', 'volunteer_tasks.id', '=', 'task_assignments.volunteer_task_id')
            ->where('task_assignments.volunteer_profile_id', $profile->id)
            ->whereIn('task_assignments.status', ['ditawarkan', 'diterima'])
            ->whereIn('volunteer_tasks.status', ['terbuka', 'berjalan'])
            ->exists();
    }

    public function taskHistory()
    {
        $profile = $this->profile();

        if (! $profile) {
            return collect();
        }

        return DB::table('task_assignments')
            ->join('volunteer_tasks', 'volunteer_tasks.id', '=', 'task_assignments.volunteer_task_id')
            ->where('task_assignments.volunteer_profile_id', $profile->id)
            ->orderByDesc('volunteer_tasks.start_at')
            ->select(
                'volunteer_tasks.title',
                'volunteer_tasks.start_at',
                'volunteer_tasks.status as task_status',
                'task_assignments.status as assignment_status',
            )
            ->limit(10)
            ->get();
    }

    protected function notify(string $message, string $type = 'success'): void
    {
        $this->notification = $message;
        $this->notificationType = $type;
    }

    public function dismissNotification(): void
    {
        $this->notification = null;
    }

    public function render()
    {
        $profile = $this->profile();
        $regencyOptions = self::REGENCIES;

        if ($this->regency && ! in_array($this->regency, $regencyOptions, true)) {
            array_unshift($regencyOptions, $this->regency);
        }

        return view('livewire.volunteer.profile-manager', [
            'profile' => $profile,
            'skills' => Skill::orderBy('id')->get(),
            'taskHistory' => $this->taskHistory(),
            'hasActiveTask' => $this->hasActiveTask(),
            'medicalSelected' => $this->hasMedicalSkill(),
            'regencyOptions' => $regencyOptions,
        ]);
    }
}
