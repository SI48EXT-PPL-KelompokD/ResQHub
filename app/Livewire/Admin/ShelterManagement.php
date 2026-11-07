<?php

namespace App\Livewire\Admin;

use App\Models\DisasterEvent;
use App\Models\Shelter;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Manajemen Posko Evakuasi - ResQHub')]
class ShelterManagement extends Component
{
    use WithPagination;

    // Filter & Search
    public string $search = '';
    public string $statusFilter = '';
    public string $eventFilter = '';

    // Modal state
    public bool $showCreateModal = false;

    // Form inputs (Tambah Posko)
    public $disaster_event_id = '';
    public string $name = '';
    public string $address = '';
    public $lat = -6.9935000;
    public $lng = 107.6250000;
    public $capacity = '';
    public string $contact = '';

    // Toast Notification
    public ?array $notification = null;

    protected string $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $user = auth()->user();

        // Hanya Koordinator Posko dan Admin yang memiliki izin mengelola posko
        if (! $user || (! $user->isKoordinator() && ! $user->isAdmin())) {
            abort(403, 'Akses Ditolak: Hanya Koordinator Posko dan Admin yang berwenang mengelola data posko.');
        }

        $firstActive = DisasterEvent::active()->first();
        if ($firstActive) {
            $this->disaster_event_id = $firstActive->id;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingEventFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();

        // Defaultkan ke kejadian bencana aktif pertama jika ada
        $firstActive = DisasterEvent::active()->first();
        if ($firstActive) {
            $this->disaster_event_id = $firstActive->id;
        }

        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->disaster_event_id = '';
        $this->name = '';
        $this->address = '';
        $this->lat = -6.9935000;
        $this->lng = 107.6250000;
        $this->capacity = '';
        $this->contact = '';
    }

    public function dismissNotification(): void
    {
        $this->notification = null;
    }

    /**
     * Simpan data posko baru sesuai acceptance criteria PBI-04:
     * - Kejadian terkait harus aktif
     * - Kapasitas lebih dari 0
     * - Posko otomatis berstatus 'aktif'
     * - Pendaftar otomatis tercatat sebagai koordinator/pengelola
     */
    public function store(): void
    {
        $this->validate([
            'disaster_event_id' => [
                'required',
                'exists:disaster_events,id',
                function ($attribute, $value, $fail) {
                    $event = DisasterEvent::find($value);
                    if (! $event || $event->status !== 'aktif') {
                        $fail('Kejadian bencana yang dipilih harus berstatus aktif.');
                    }
                },
            ],
            'name' => 'required|string|max:150',
            'address' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'capacity' => 'required|integer|min:1',
            'contact' => 'required|string|max:50',
        ], [
            'disaster_event_id.required' => 'Pilih kejadian bencana terkait.',
            'name.required' => 'Nama posko wajib diisi.',
            'name.max' => 'Nama posko maksimal 150 karakter.',
            'address.required' => 'Alamat posko wajib diisi.',
            'lat.required' => 'Titik koordinat latitude wajib ditentukan.',
            'lng.required' => 'Titik koordinat longitude wajib ditentukan.',
            'capacity.required' => 'Kapasitas daya tampung posko wajib diisi.',
            'capacity.min' => 'Kapasitas daya tampung harus lebih dari 0 jiwa.',
            'contact.required' => 'Nomor kontak narahubung posko wajib diisi.',
        ]);

        $shelter = Shelter::create([
            'disaster_event_id' => $this->disaster_event_id,
            'coordinator_id'    => auth()->id(),
            'name'              => trim($this->name),
            'address'           => trim($this->address),
            'lat'               => (float) $this->lat,
            'lng'               => (float) $this->lng,
            'capacity'          => (int) $this->capacity,
            'occupancy'         => 0,
            'contact'           => trim($this->contact),
            'status'            => 'aktif',
            'close_reason'      => null,
        ]);

        $this->closeCreateModal();
        $this->dispatch('shelter-created');

        $this->notification = [
            'type'    => 'success',
            'message' => "Posko '{$shelter->name}' berhasil didaftarkan dan berstatus Aktif.",
        ];
    }

    public function render()
    {
        $user = auth()->user();

        $query = Shelter::query()->with(['disasterEvent', 'coordinator']);

        // Jika bukan admin (koordinator posko), batasi hanya melihat posko kelolaan sendiri
        if (! $user->isAdmin()) {
            $query->where('coordinator_id', $user->id);
        }

        // Filter pencarian teks
        if (! empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('address', 'like', $searchTerm)
                  ->orWhere('contact', 'like', $searchTerm)
                  ->orWhereHas('disasterEvent', function ($sub) use ($searchTerm) {
                      $sub->where('name', 'like', $searchTerm);
                  });
            });
        }

        // Filter status posko
        if (! empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        // Filter kejadian bencana
        if (! empty($this->eventFilter)) {
            $query->where('disaster_event_id', $this->eventFilter);
        }

        $shelters = $query->latest()->paginate(10);
        $activeEvents = DisasterEvent::active()->orderBy('name')->get();

        return view('livewire.admin.shelter-management', [
            'shelters'     => $shelters,
            'activeEvents' => $activeEvents,
        ]);
    }
}
