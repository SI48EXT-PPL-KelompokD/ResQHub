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

    public function render(DisasterReportService $service)
    {
        return view('livewire.report.my-reports', [
            'reports' => $service->listForReporter(auth()->user()),
            'statusMessage' => session('status'),
        ]);
    }
}
