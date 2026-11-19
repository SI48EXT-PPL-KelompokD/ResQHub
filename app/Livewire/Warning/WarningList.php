<?php

namespace App\Livewire\Warning;

use App\Models\EarlyWarning;
use App\Services\EarlyWarningService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Peringatan Dini - ResQHub')]
class WarningList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    #[Url(except: '')]
    public string $status = '';

    #[Url(as: 'tingkat', except: '')]
    public string $level = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['status', 'level'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['status', 'level']);
        $this->resetPage();
    }

    public function render(EarlyWarningService $service)
    {
        $filters = $this->filters();

        return view('livewire.warning.warning-list', [
            'warnings' => $service->listForAdmin(auth()->user(), $filters),
            'statuses' => EarlyWarning::statusLabels(),
            'levels' => EarlyWarning::LEVELS,
            'activeCount' => EarlyWarning::query()->where('status', EarlyWarning::STATUS_ACTIVE)->count(),
            'hasFilters' => array_filter($filters) !== [],
        ]);
    }

    /**
     * AC 2: filter status (Draf, Aktif, Kedaluwarsa, Dicabut) dan tingkat.
     *
     * @return array{status: ?string, level: ?string}
     */
    private function filters(): array
    {
        return [
            'status' => array_key_exists($this->status, EarlyWarning::statusLabels()) ? $this->status : null,
            'level' => in_array($this->level, EarlyWarning::LEVELS, true) ? $this->level : null,
        ];
    }
}
