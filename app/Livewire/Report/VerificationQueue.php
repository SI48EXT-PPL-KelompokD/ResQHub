<?php

namespace App\Livewire\Report;

use App\Models\DisasterReport;
use App\Services\DisasterReportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Verifikasi Laporan Kejadian - ResQHub')]
class VerificationQueue extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    #[Url(as: 'jenis', except: '')]
    public string $type = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(as: 'wilayah', except: '')]
    public string $area = '';

    #[Url(as: 'dari', except: '')]
    public string $dateFrom = '';

    #[Url(as: 'sampai', except: '')]
    public string $dateTo = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    /**
     * Setiap perubahan filter kembali ke halaman pertama dan menggambar ulang peta sebaran.
     */
    public function updated(string $property): void
    {
        if (in_array($property, ['type', 'status', 'area', 'dateFrom', 'dateTo'], true)) {
            $this->refreshList();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['type', 'status', 'area', 'dateFrom', 'dateTo']);
        $this->refreshList();
    }

    public function render(DisasterReportService $service)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return view('livewire.report.verification-queue', [
            'reports' => $service->listForAdmin($this->filters()),
            'points' => $service->mapPointsForAdmin($this->filters()),
            'pendingCount' => DisasterReport::pending()->count(),
            'disasterTypes' => DisasterReport::DISASTER_TYPES,
            'statuses' => [
                DisasterReport::STATUS_PENDING => 'Menunggu Verifikasi',
                DisasterReport::STATUS_ACCEPTED => 'Diterima',
                DisasterReport::STATUS_REJECTED => 'Ditolak',
            ],
            'hasFilters' => array_filter($this->filters()) !== [],
        ]);
    }

    private function refreshList(): void
    {
        $this->resetPage();

        $this->dispatch('report-points-updated', points: app(DisasterReportService::class)->mapPointsForAdmin($this->filters())->all());
    }

    /**
     * @return array{disaster_type: ?string, status: ?string, area: ?string, date_from: ?string, date_to: ?string}
     */
    private function filters(): array
    {
        return [
            'disaster_type' => in_array($this->type, DisasterReport::DISASTER_TYPES, true) ? $this->type : null,
            'status' => $this->status !== '' ? $this->status : null,
            'area' => trim($this->area) !== '' ? trim($this->area) : null,
            'date_from' => $this->validDate($this->dateFrom),
            'date_to' => $this->validDate($this->dateTo),
        ];
    }

    private function validDate(string $value): ?string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : null;
    }
}
