<?php

namespace App\Livewire\Admin;

use App\Models\AidNeed;
use App\Models\Item;
use App\Models\Shelter;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Form Pengajuan Kebutuhan Posko (PBI-11) - ResQHub')]
class AidNeedForm extends Component
{
    public string|int $shelter_id = '';
    public string|int $item_id = '';
    public string|int $qty_needed = '';
    public string $priority = 'tinggi';
    public ?string $deadline = null;
    public ?string $notes = '';

    public ?Item $selectedItem = null;
    public ?Shelter $selectedShelter = null;
    public ?Stock $currentStock = null;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isKoordinator() && ! $user->isAdmin())) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang mengakses form pengajuan kebutuhan bantuan.');
        }

        // Ambil query parameter jika ada
        $this->item_id = request()->query('item_id', '');
        $this->shelter_id = request()->query('shelter_id', '');

        // Jika user koordinator dan posko belum dipilih, defaultkan ke poskonya
        if (! $this->shelter_id && $user->isKoordinator()) {
            $first = Shelter::where('coordinator_id', $user->id)->first();
            if ($first) {
                $this->shelter_id = $first->id;
            }
        }

        $this->deadline = Carbon::now()->addDays(3)->format('Y-m-d');

        $this->syncSelection();
    }

    public function updatedItemId(): void
    {
        $this->syncSelection();
    }

    public function updatedShelterId(): void
    {
        $this->syncSelection();
    }

    private function syncSelection(): void
    {
        $this->selectedItem = $this->item_id ? Item::find($this->item_id) : null;
        $this->selectedShelter = $this->shelter_id ? Shelter::find($this->shelter_id) : null;

        if ($this->selectedItem && $this->selectedShelter) {
            $this->currentStock = Stock::where('shelter_id', $this->selectedShelter->id)
                ->where('item_id', $this->selectedItem->id)
                ->first();

            if ($this->currentStock && ! $this->qty_needed) {
                $deficit = $this->currentStock->min_threshold - $this->currentStock->quantity;
                $this->qty_needed = max(1, $deficit);
                $this->priority = $this->currentStock->quantity <= 0 ? 'mendesak' : ($this->currentStock->isDepleted() ? 'tinggi' : 'normal');
            }
        } else {
            $this->currentStock = null;
        }
    }

    public function submit()
    {
        $this->validate([
            'shelter_id' => ['required', 'exists:shelters,id'],
            'item_id' => ['required', 'exists:items,id'],
            'qty_needed' => ['required', 'integer', 'min:1'],
            'priority' => ['required', 'in:mendesak,tinggi,normal'],
            'deadline' => ['nullable', 'date'],
        ], [
            'shelter_id.required' => 'Posko wajib dipilih.',
            'item_id.required' => 'Barang bantuan wajib dipilih.',
            'qty_needed.required' => 'Jumlah kebutuhan bantuan wajib diisi.',
            'qty_needed.min' => 'Jumlah kebutuhan minimal 1.',
        ]);

        $user = Auth::user();
        if ($user->isKoordinator()) {
            $isOwner = Shelter::where('id', $this->shelter_id)
                ->where('coordinator_id', $user->id)
                ->exists();
            if (! $isOwner) {
                abort(403, 'Anda tidak berwenang mengajukan kebutuhan untuk posko ini.');
            }
        }

        AidNeed::create([
            'shelter_id' => $this->shelter_id,
            'item_id' => $this->item_id,
            'qty_needed' => (int) $this->qty_needed,
            'qty_pledged' => 0,
            'qty_fulfilled' => 0,
            'priority' => $this->priority,
            'deadline' => $this->deadline,
            'status' => 'terbuka',
            'close_reason' => $this->notes,
        ]);

        session()->flash('notification', [
            'type' => 'success',
            'message' => 'Pengajuan kebutuhan posko (PBI-11) berhasil dibuat dengan status terbuka.',
        ]);

        return redirect()->route('admin.stocks.index');
    }

    public function render()
    {
        $user = Auth::user();
        $shelters = $user->isAdmin()
            ? Shelter::orderBy('name')->get()
            : Shelter::where('coordinator_id', $user->id)->orderBy('name')->get();

        $items = Item::where('is_active', true)->orderBy('name')->get();

        return view('livewire.admin.aid-need-form', [
            'shelters' => $shelters,
            'items' => $items,
        ]);
    }
}

