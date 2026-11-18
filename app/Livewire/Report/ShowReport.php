<?php

namespace App\Livewire\Report;

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
    private const HISTORY_LABELS = [
        'laporan_dibuat' => 'Laporan dikirim',
        'laporan_diubah' => 'Laporan diubah',
        'laporan_diterima' => 'Laporan diterima',
        'laporan_ditolak' => 'Laporan ditolak',
    ];

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
            'history' => $service->history($report)->map(fn ($log) => [
                'label' => self::HISTORY_LABELS[$log->action] ?? ucfirst(str_replace('_', ' ', $log->action)),
                'action' => $log->action,
                'actor' => $log->user?->name ?? 'Sistem',
                'at' => $log->created_at,
                'note' => $this->historyNote($log->action, $log->changes ?? []),
            ]),
            'statusMessage' => session('status'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    private function historyNote(string $action, array $changes): ?string
    {
        return match ($action) {
            'laporan_dibuat' => 'Status: Menunggu Verifikasi',
            'laporan_diterima' => 'Status: Diterima dan dikaitkan ke kejadian bencana.',
            'laporan_ditolak' => 'Status: Ditolak. Alasan: '.($changes['reject_reason'] ?? '-'),
            default => null,
        };
    }
}
