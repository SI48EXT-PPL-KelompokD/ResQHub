<?php

namespace App\Livewire\Report;

use App\Livewire\Report\Concerns\PresentsReportHistory;
use App\Models\DisasterReport;
use App\Services\DisasterReportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Detail Laporan Kejadian - ResQHub')]
class ShowReport extends Component
{
    use PresentsReportHistory;

    #[Locked]
    public int $reportId;

    public function mount(DisasterReport $report): void
    {
        $this->authorize('view', $report);

        $this->reportId = $report->id;
    }

    /**
     * AC 4: hapus hanya untuk laporan yang masih menunggu verifikasi.
     */
    public function delete(DisasterReportService $service)
    {
        $report = DisasterReport::findOrFail($this->reportId);

        $this->authorize('delete', $report);

        $service->delete(auth()->user(), $report);

        session()->flash('status', 'Laporan berhasil dihapus.');

        return $this->redirectRoute('admin.reports.index', navigate: true);
    }

    public function render(DisasterReportService $service)
    {
        $report = DisasterReport::with(['photos', 'disasterEvent', 'verifier'])->findOrFail($this->reportId);

        $this->authorize('view', $report);

        return view('livewire.report.show-report', [
            'report' => $report,
            'history' => $this->presentHistory($service, $report),
            'statusMessage' => session('status'),
        ]);
    }
}
