<?php

namespace App\Livewire\Report\Concerns;

use App\Models\DisasterReport;
use App\Services\DisasterReportService;
use Illuminate\Support\Collection;

/**
 * Menyiapkan riwayat status laporan agar terbaca di halaman detail warga dan admin.
 */
trait PresentsReportHistory
{
    /**
     * @return Collection<int, array{label: string, action: string, actor: string, at: mixed, note: ?string}>
     */
    protected function presentHistory(DisasterReportService $service, DisasterReport $report): Collection
    {
        $labels = [
            'laporan_dibuat' => 'Laporan dikirim',
            'laporan_diubah' => 'Laporan diubah',
            'laporan_diterima' => 'Laporan diterima',
            'laporan_ditolak' => 'Laporan ditolak',
        ];

        return $service->history($report)->map(fn ($log) => [
            'label' => $labels[$log->action] ?? ucfirst(str_replace('_', ' ', $log->action)),
            'action' => $log->action,
            'actor' => $log->user?->name ?? 'Sistem',
            'at' => $log->created_at,
            'note' => $this->historyNote($log->action, $log->changes ?? []),
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
