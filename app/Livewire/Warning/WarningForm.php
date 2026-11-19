<?php

namespace App\Livewire\Warning;

use App\Http\Requests\EarlyWarning\StoreEarlyWarningRequest;
use App\Http\Requests\EarlyWarning\UpdateEarlyWarningRequest;
use App\Livewire\Auth\Register;
use App\Models\DisasterEvent;
use App\Models\DisasterReport;
use App\Models\EarlyWarning;
use App\Services\EarlyWarningService;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Form tambah dan ubah peringatan dini (PBI-02 AC 1 dan AC 3).
 */
#[Layout('layouts.admin')]
#[Title('Peringatan Dini - ResQHub')]
class WarningForm extends Component
{
    #[Locked]
    public ?int $warningId = null;

    public string $title = '';

    public string $hazard_type = '';

    public string $level = '';

    /** @var list<string> */
    public array $target_regencies = [];

    /** Wilayah sasaran tambahan berupa radius dari titik di peta. */
    public bool $useRadius = false;

    public $center_lat = null;

    public $center_lng = null;

    public $radius_km = null;

    public string $instruction = '';

    public string $starts_at = '';

    public string $ends_at = '';

    public $disaster_event_id = '';

    /** Catatan pembaruan, hanya untuk mode ubah. */
    public string $note = '';

    public function mount(?EarlyWarning $warning = null): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if ($warning?->exists) {
            $this->authorize('update', $warning);

            $this->warningId = $warning->id;
            $this->title = $warning->title;
            $this->hazard_type = $warning->hazard_type;
            $this->level = $warning->level;
            $this->target_regencies = $warning->target_regencies ?? [];
            $this->useRadius = $warning->radius_km !== null;
            $this->center_lat = $warning->center_lat;
            $this->center_lng = $warning->center_lng;
            $this->radius_km = $warning->radius_km;
            $this->instruction = $warning->instruction;
            $this->starts_at = $warning->starts_at?->format('Y-m-d\TH:i') ?? '';
            $this->ends_at = $warning->ends_at?->format('Y-m-d\TH:i') ?? '';
            $this->disaster_event_id = $warning->disaster_event_id ?? '';

            return;
        }

        $this->starts_at = now()->format('Y-m-d\TH:i');
        $this->ends_at = now()->addDay()->format('Y-m-d\TH:i');
    }

    /**
     * AC 1: simpan sebagai draf.
     */
    public function saveDraft(EarlyWarningService $service): void
    {
        $this->store($service, false);
    }

    /**
     * AC 1: langsung terbitkan sehingga warga di wilayah sasaran menerima notifikasi.
     */
    public function publish(EarlyWarningService $service): void
    {
        $this->store($service, true);
    }

    /**
     * AC 3: simpan perubahan; kenaikan tingkat memicu notifikasi ulang di service.
     */
    public function update(EarlyWarningService $service): void
    {
        $warning = EarlyWarning::findOrFail($this->warningId);

        $this->authorize('update', $warning);

        $data = $this->validated(new UpdateEarlyWarningRequest);
        $note = $data['note'] ?? null;
        unset($data['note']);

        $service->update(auth()->user(), $warning, $data, $note);

        session()->flash('status', 'Perubahan peringatan disimpan dan tercatat di riwayat pembaruan.');

        $this->redirectRoute('admin.warnings.show', ['warning' => $warning->id], navigate: true);
    }

    public function render()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return view('livewire.warning.warning-form', [
            'isEdit' => $this->warningId !== null,
            'hazardTypes' => DisasterReport::DISASTER_TYPES,
            'levels' => EarlyWarning::LEVELS,
            'regencies' => array_values(array_unique([...Register::REGENCIES, ...$this->target_regencies])),
            'events' => DisasterEvent::active()->orderByDesc('started_at')->get(['id', 'name', 'regency']),
        ]);
    }

    private function store(EarlyWarningService $service, bool $publish): void
    {
        $data = $this->validated(new StoreEarlyWarningRequest);

        $warning = $service->create(auth()->user(), $data, $publish);

        session()->flash('status', $publish
            ? 'Peringatan diterbitkan. Warga di wilayah sasaran sudah menerima notifikasi.'
            : 'Peringatan disimpan sebagai draf.');

        $this->redirectRoute('admin.warnings.show', ['warning' => $warning->id], navigate: true);
    }

    /**
     * Isian kosong dinormalisasi menjadi null sebelum divalidasi dengan aturan FormRequest,
     * dan titik peta diabaikan bila radius tidak dipakai agar tidak tersimpan titik tanpa radius.
     *
     * @return array<string, mixed>
     */
    private function validated(StoreEarlyWarningRequest $request): array
    {
        $regencies = array_values(array_filter($this->target_regencies, fn ($regency) => trim((string) $regency) !== ''));

        $data = [
            'title' => trim($this->title),
            'hazard_type' => $this->hazard_type,
            'level' => $this->level,
            'target_regencies' => $regencies === [] ? null : $regencies,
            'center_lat' => $this->useRadius ? $this->blankToNull($this->center_lat) : null,
            'center_lng' => $this->useRadius ? $this->blankToNull($this->center_lng) : null,
            'radius_km' => $this->useRadius ? $this->blankToNull($this->radius_km) : null,
            'instruction' => trim($this->instruction),
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'disaster_event_id' => $this->blankToNull($this->disaster_event_id),
        ];

        $rules = $request->rules();
        unset($rules['publish']);

        if (array_key_exists('note', $rules)) {
            $data['note'] = trim($this->note) !== '' ? trim($this->note) : null;
        }

        $messages = $request->messages() + [
            'radius_km.integer' => 'Radius harus berupa bilangan bulat (km).',
            'radius_km.min' => 'Radius minimal 1 km.',
            'radius_km.max' => 'Radius maksimal 500 km.',
            'title.max' => 'Judul maksimal 255 karakter.',
            'instruction.max' => 'Instruksi maksimal 2000 karakter.',
            'note.max' => 'Catatan pembaruan maksimal 1000 karakter.',
        ];

        Validator::make($data, $rules, $messages)->validate();

        return $data;
    }

    private function blankToNull(mixed $value): mixed
    {
        return $value === '' || $value === null ? null : $value;
    }
}
