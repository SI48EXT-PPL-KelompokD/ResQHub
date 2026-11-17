<?php

namespace App\Livewire\Report;

use App\Livewire\Report\Concerns\PresentsReportHistory;
use App\Models\DisasterEvent;
use App\Models\DisasterReport;
use App\Services\DisasterReportService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Verifikasi Laporan Kejadian - ResQHub')]
class VerifyReport extends Component
{
    use PresentsReportHistory;

    public const SEVERITIES = ['ringan', 'sedang', 'berat'];

    #[Locked]
    public int $reportId;

    /** Cara mengaitkan laporan yang diterima: kejadian yang ada (existing) atau kejadian baru (new). */
    public string $eventMode = 'existing';

    public $disaster_event_id = '';

    public string $new_event_name = '';

    public string $new_event_severity = '';

    public string $new_event_regency = '';

    public string $reject_reason = '';

    public function mount(DisasterReport $report): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $this->reportId = $report->id;

        if (! DisasterEvent::active()->exists()) {
            $this->eventMode = 'new';
        }
    }

    /**
     * AC 3: terima laporan dan kaitkan ke kejadian yang ada atau kejadian baru.
     */
    public function accept(DisasterReportService $service): void
    {
        $report = DisasterReport::findOrFail($this->reportId);

        $this->authorize('verify', $report);

        if ($this->eventMode === 'new') {
            $validated = $this->validate([
                'new_event_name' => ['required', 'string', 'max:150'],
                'new_event_severity' => ['required', Rule::in(self::SEVERITIES)],
                'new_event_regency' => ['required', 'string', 'max:100'],
            ], [
                'new_event_name.required' => 'Nama kejadian baru wajib diisi.',
                'new_event_name.max' => 'Nama kejadian maksimal 150 karakter.',
                'new_event_severity.required' => 'Tingkat keparahan kejadian baru wajib dipilih.',
                'new_event_severity.in' => 'Tingkat keparahan tidak valid.',
                'new_event_regency.required' => 'Kabupaten/kota kejadian baru wajib diisi.',
                'new_event_regency.max' => 'Kabupaten/kota maksimal 100 karakter.',
            ]);

            $service->accept(auth()->user(), $report, null, [
                'name' => trim($validated['new_event_name']),
                'severity' => $validated['new_event_severity'],
                'regency' => trim($validated['new_event_regency']),
            ]);
        } else {
            $validated = $this->validate([
                'disaster_event_id' => ['required', 'integer', 'exists:disaster_events,id'],
            ], [
                'disaster_event_id.required' => 'Pilih kejadian yang ada atau buat kejadian baru.',
                'disaster_event_id.integer' => 'Kejadian bencana tidak valid.',
                'disaster_event_id.exists' => 'Kejadian bencana tidak ditemukan.',
            ]);

            $service->accept(auth()->user(), $report, (int) $validated['disaster_event_id']);
        }

        $this->reset(['disaster_event_id', 'new_event_name', 'new_event_severity', 'new_event_regency', 'reject_reason']);

        session()->flash('status', 'Laporan diterima dan dikaitkan ke kejadian bencana. Pelapor sudah menerima notifikasi.');
    }

    /**
     * AC 3: tolak laporan dengan alasan wajib.
     */
    public function reject(DisasterReportService $service): void
    {
        $report = DisasterReport::findOrFail($this->reportId);

        $this->authorize('verify', $report);

        $validated = $this->validate([
            'reject_reason' => ['required', 'string', 'max:1000'],
        ], [
            'reject_reason.required' => 'Alasan penolakan wajib diisi.',
            'reject_reason.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ]);

        $service->reject(auth()->user(), $report, $validated['reject_reason']);

        $this->reset(['disaster_event_id', 'new_event_name', 'new_event_severity', 'new_event_regency', 'reject_reason']);

        session()->flash('status', 'Laporan ditolak. Pelapor sudah menerima notifikasi beserta alasannya.');
    }

    public function render(DisasterReportService $service)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $report = DisasterReport::with(['photos', 'disasterEvent', 'verifier', 'reporter'])->findOrFail($this->reportId);

        return view('livewire.report.verify-report', [
            'report' => $report,
            'history' => $this->presentHistory($service, $report),
            'nearby' => $service->nearbyReports($report),
            'radiusKm' => DisasterReportService::DUPLICATE_RADIUS_KM,
            'events' => $report->isPending()
                ? DisasterEvent::active()->orderByDesc('started_at')->get(['id', 'name', 'disaster_type', 'regency'])
                : collect(),
            'severities' => self::SEVERITIES,
            'statusMessage' => session('status'),
        ]);
    }
}
