<?php

namespace App\Livewire\Report;

use App\Models\DisasterReport;
use App\Services\DisasterReportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Laporan Saya - ResQHub')]
class MyReports extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->authorize('viewAny', DisasterReport::class);
    }

    /**
     * AC 4: hapus hanya untuk laporan milik sendiri yang masih menunggu verifikasi.
     */
    public function delete(int $reportId, DisasterReportService $service): void
    {
        $report = DisasterReport::findOrFail($reportId);

        $this->authorize('delete', $report);

        $service->delete(auth()->user(), $report);

        session()->flash('status', 'Laporan berhasil dihapus.');

        $this->resetPage();
    }

    public function render(DisasterReportService $service)
    {
        return view('livewire.report.my-reports', [
            'reports' => $service->listForReporter(auth()->user()),
            'statusMessage' => session('status'),
        ]);
    }
}
