<?php

namespace App\Livewire\Warning;

use App\Http\Requests\EarlyWarning\RevokeEarlyWarningRequest;
use App\Models\EarlyWarning;
use App\Models\WarningUpdate;
use App\Services\EarlyWarningService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Detail peringatan dini untuk admin: jumlah penerima, riwayat pembaruan,
 * terbitkan draf, hapus draf, dan cabut peringatan aktif (PBI-02 AC 1, 2, 4).
 */
#[Layout('layouts.admin')]
#[Title('Detail Peringatan Dini - ResQHub')]
class ShowWarning extends Component
{
    #[Locked]
    public int $warningId;

    public string $revoke_reason = '';

    public function mount(EarlyWarning $warning): void
    {
        $this->authorize('view', $warning);

        $this->warningId = $warning->id;
    }

    public function publish(EarlyWarningService $service): void
    {
        $warning = EarlyWarning::findOrFail($this->warningId);

        $this->authorize('publish', $warning);

        $service->publish(auth()->user(), $warning);

        session()->flash('status', 'Peringatan diterbitkan. Warga di wilayah sasaran sudah menerima notifikasi.');
    }

    public function deleteDraft(EarlyWarningService $service): void
    {
        $warning = EarlyWarning::findOrFail($this->warningId);

        $this->authorize('delete', $warning);

        $service->deleteDraft(auth()->user(), $warning);

        session()->flash('status', 'Draf peringatan dihapus permanen.');

        $this->redirectRoute('admin.warnings.index', navigate: true);
    }

    public function revoke(EarlyWarningService $service): void
    {
        $warning = EarlyWarning::findOrFail($this->warningId);

        $this->authorize('revoke', $warning);

        $request = new RevokeEarlyWarningRequest;
        $this->revoke_reason = trim($this->revoke_reason);
        $validated = $this->validate($request->rules(), $request->messages());

        $service->revoke(auth()->user(), $warning, $validated['revoke_reason']);

        $this->reset('revoke_reason');

        session()->flash('status', 'Peringatan dicabut. Warga terdampak sudah menerima pemberitahuan pencabutan.');
    }

    public function render(EarlyWarningService $service)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $warning = EarlyWarning::with(['issuer', 'disasterEvent'])->findOrFail($this->warningId);

        return view('livewire.warning.show-warning', [
            'warning' => $warning,
            'recipientCount' => $service->recipientCount($warning),
            'history' => $service->history($warning)->map(fn (WarningUpdate $update) => [
                'old_level' => EarlyWarning::levelLabel($update->old_level),
                'new_level' => EarlyWarning::levelLabel($update->new_level),
                'direction' => EarlyWarning::levelRank($update->new_level) <=> EarlyWarning::levelRank($update->old_level),
                'note' => $update->note,
                'by' => $update->updater?->name ?? '-',
                'at' => $update->created_at,
            ]),
            'statusMessage' => session('status'),
        ]);
    }
}
