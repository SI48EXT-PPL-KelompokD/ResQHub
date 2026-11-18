<?php

namespace App\Livewire\Report;

use App\Http\Requests\DisasterReport\StoreDisasterReportRequest;
use App\Models\DisasterReport;
use App\Services\DisasterReportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
#[Title('Buat Laporan Kejadian - ResQHub')]
class CreateReport extends Component
{
    use WithFileUploads;

    public string $disaster_type = '';

    public string $title = '';

    public string $description = '';

    public $estimated_affected = null;

    public $lat = null;

    public $lng = null;

    public string $address = '';

    public string $occurred_at = '';

    /** Foto terpilih yang akan dikirim (maks 3). */
    public array $photos = [];

    /** Penampung pilihan file baru sebelum digabung ke $photos. */
    public array $newPhotos = [];

    public function mount(): void
    {
        $this->authorize('create', DisasterReport::class);

        $this->occurred_at = now()->format('Y-m-d\TH:i');
    }

    public function updatedNewPhotos(): void
    {
        $this->validate([
            'newPhotos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'newPhotos.*.image' => 'File harus berupa gambar.',
            'newPhotos.*.mimes' => 'Foto harus berformat JPG atau PNG.',
            'newPhotos.*.max' => 'Ukuran setiap foto maksimal 2 MB.',
        ]);

        $this->photos = array_values(array_merge($this->photos, $this->newPhotos));
        $this->newPhotos = [];

        if (count($this->photos) > DisasterReport::MAX_PHOTOS) {
            $this->photos = array_slice($this->photos, 0, DisasterReport::MAX_PHOTOS);
            $this->addError('photos', 'Maksimal '.DisasterReport::MAX_PHOTOS.' foto per laporan.');
        }
    }

    public function removePhoto(int $index): void
    {
        unset($this->photos[$index]);
        $this->photos = array_values($this->photos);
    }

    public function save(DisasterReportService $service)
    {
        $this->authorize('create', DisasterReport::class);

        $request = new StoreDisasterReportRequest;

        $validated = $this->validate($request->rules(), $request->messages());
        $validated['estimated_affected'] = ($validated['estimated_affected'] ?? '') === '' ? null : (int) $validated['estimated_affected'];

        $service->create(auth()->user(), $validated, $this->photos);

        session()->flash('status', 'Laporan berhasil dikirim dan sedang menunggu verifikasi Admin Pusdalops.');

        return $this->redirectRoute('admin.reports.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.report.create-report', [
            'disasterTypes' => DisasterReport::DISASTER_TYPES,
            'maxPhotos' => DisasterReport::MAX_PHOTOS,
        ]);
    }
}
