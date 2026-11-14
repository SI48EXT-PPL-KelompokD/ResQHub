<?php

namespace App\Livewire\Report;

use App\Http\Requests\DisasterReport\UpdateDisasterReportRequest;
use App\Models\DisasterReport;
use App\Services\DisasterReportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
#[Title('Ubah Laporan Kejadian - ResQHub')]
class EditReport extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $reportId;

    public string $disaster_type = '';

    public string $title = '';

    public string $description = '';

    public $estimated_affected = null;

    public $lat = null;

    public $lng = null;

    public string $address = '';

    public string $occurred_at = '';

    /** Foto baru yang akan ditambahkan. */
    public array $photos = [];

    /** Penampung pilihan file baru sebelum digabung ke $photos. */
    public array $newPhotos = [];

    /** ID foto lama yang ditandai untuk dihapus saat disimpan. */
    public array $removed_photo_ids = [];

    public function mount(DisasterReport $report): void
    {
        $this->authorize('update', $report);

        $this->reportId = $report->id;
        $this->disaster_type = $report->disaster_type;
        $this->title = $report->title;
        $this->description = $report->description;
        $this->estimated_affected = $report->estimated_affected;
        $this->lat = $report->lat;
        $this->lng = $report->lng;
        $this->address = $report->address;
        $this->occurred_at = $report->occurred_at->format('Y-m-d\TH:i');
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

        $slots = $this->availableSlots();

        if (count($this->photos) > $slots) {
            $this->photos = array_slice($this->photos, 0, $slots);
            $this->addError('photos', 'Maksimal '.DisasterReport::MAX_PHOTOS.' foto per laporan.');
        }
    }

    public function removePhoto(int $index): void
    {
        unset($this->photos[$index]);
        $this->photos = array_values($this->photos);
    }

    /**
     * Tandai foto lama untuk dihapus; foto baru benar-benar terhapus saat laporan disimpan.
     */
    public function removeExistingPhoto(int $photoId): void
    {
        $owned = $this->report()->photos()->whereKey($photoId)->exists();

        if ($owned && ! in_array($photoId, $this->removed_photo_ids, true)) {
            $this->removed_photo_ids[] = $photoId;
        }
    }

    public function restoreExistingPhoto(int $photoId): void
    {
        $restored = array_values(array_diff($this->removed_photo_ids, [$photoId]));
        $kept = $this->report()->photos()->whereNotIn('id', $restored)->count();

        if ($kept + count($this->photos) > DisasterReport::MAX_PHOTOS) {
            $this->addError('photos', 'Maksimal '.DisasterReport::MAX_PHOTOS.' foto per laporan. Hapus foto baru terlebih dahulu.');

            return;
        }

        $this->removed_photo_ids = $restored;
    }

    public function save(DisasterReportService $service)
    {
        $report = $this->report();

        $this->authorize('update', $report);

        $request = new UpdateDisasterReportRequest;

        $validated = $this->validate($request->rules(), $request->messages());
        $validated['estimated_affected'] = ($validated['estimated_affected'] ?? '') === '' ? null : (int) $validated['estimated_affected'];

        $service->update(auth()->user(), $report, $validated, $this->photos, $this->removed_photo_ids);

        session()->flash('status', 'Perubahan laporan berhasil disimpan.');

        return $this->redirectRoute('admin.reports.show', $report, navigate: true);
    }

    public function render()
    {
        $report = $this->report();

        return view('livewire.report.edit-report', [
            'report' => $report,
            'existingPhotos' => $report->photos,
            'disasterTypes' => DisasterReport::DISASTER_TYPES,
            'maxPhotos' => DisasterReport::MAX_PHOTOS,
            'availableSlots' => $this->availableSlots(),
        ]);
    }

    private function report(): DisasterReport
    {
        return DisasterReport::with('photos')->findOrFail($this->reportId);
    }

    /**
     * Sisa slot foto baru setelah dikurangi foto lama yang dipertahankan.
     */
    private function availableSlots(): int
    {
        $kept = $this->report()->photos->whereNotIn('id', $this->removed_photo_ids)->count();

        return max(DisasterReport::MAX_PHOTOS - $kept, 0);
    }
}
