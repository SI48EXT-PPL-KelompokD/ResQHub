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
    public string $capacityFilter = '';

    // Modal state (Tambah Posko)
    public bool $showCreateModal = false;

    // Modal state (Ubah Data Posko - SCRUM-55, SCRUM-56)
    public bool $showEditModal = false;
    public ?int $editingShelterId = null;
    public $edit_disaster_event_id = '';
    public string $edit_name = '';
    public string $edit_address = '';
    public $edit_lat = -6.9935000;
    public $edit_lng = 107.6250000;
    public $edit_capacity = '';
    public string $edit_contact = '';
    public int $edit_occupancy = 0;

    // Modal state (Update Okupansi Pengungsi)
    public bool $showOccupancyModal = false;
    public ?int $selectedShelterId = null;
    public string $selectedShelterName = '';
    public int $selectedShelterCapacity = 0;
    public $newOccupancy = 0;

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

    public function updatingCapacityFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Set atau toggle filter status kapasitas dari klik kartu widget.
     */
    public function setCapacityFilter(string $status): void
    {
        $this->capacityFilter = ($this->capacityFilter === $status) ? '' : $status;
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
     * Buka modal ubah posko evakuasi kelolaan koordinator.
     */
    public function openEditModal(int $shelterId): void
    {
        $user = auth()->user();
        $query = Shelter::query();
        if (! $user->isAdmin()) {
            $query->where('coordinator_id', $user->id);
        }

        $shelter = $query->findOrFail($shelterId);

        $this->editingShelterId = $shelter->id;
        $this->edit_disaster_event_id = $shelter->disaster_event_id;
        $this->edit_name = $shelter->name;
        $this->edit_address = $shelter->address;
        $this->edit_lat = (float) $shelter->lat;
        $this->edit_lng = (float) $shelter->lng;
        $this->edit_capacity = $shelter->capacity;
        $this->edit_contact = $shelter->contact;
        $this->edit_occupancy = $shelter->occupancy;

        $this->showEditModal = true;
        $this->dispatch('render-shelter-edit-map', lat: (float) $shelter->lat, lng: (float) $shelter->lng);
    }

    /**
     * Tutup modal ubah posko.
     */
    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingShelterId = null;
        $this->resetEditForm();
        $this->resetValidation();
    }

    public function resetEditForm(): void
    {
        $this->edit_disaster_event_id = '';
        $this->edit_name = '';
        $this->edit_address = '';
        $this->edit_lat = -6.9935000;
        $this->edit_lng = 107.6250000;
        $this->edit_capacity = '';
        $this->edit_contact = '';
        $this->edit_occupancy = 0;
    }

    /**
     * Simpan pembaruan data posko evakuasi.
     * Aturan bisnis:
     * - Hanya koordinator pemilik atau admin yang berhak mengubah
     * - Kapasitas tidak boleh lebih kecil dari jumlah pengungsi aktif saat ini
     */
    public function update(): void
    {
        $user = auth()->user();
        $query = Shelter::query();
        if (! $user->isAdmin()) {
            $query->where('coordinator_id', $user->id);
        }

        $shelter = $query->findOrFail($this->editingShelterId);
        $minCapacity = max(1, (int) $shelter->occupancy);

        $this->validate([
            'edit_disaster_event_id' => [
                'required',
                'exists:disaster_events,id',
                function ($attribute, $value, $fail) use ($shelter) {
                    if ($value != $shelter->disaster_event_id) {
                        $event = DisasterEvent::find($value);
                        if (! $event || $event->status !== 'aktif') {
                            $fail('Kejadian bencana yang dipilih harus berstatus aktif.');
                        }
                    }
                },
            ],
            'edit_name'     => 'required|string|max:150',
            'edit_address'  => 'required|string|max:255',
            'edit_lat'      => 'required|numeric|between:-90,90',
            'edit_lng'      => 'required|numeric|between:-180,180',
            'edit_capacity' => [
                'required',
                'integer',
                'min:' . $minCapacity,
            ],
            'edit_contact'  => 'required|string|max:50',
        ], [
            'edit_disaster_event_id.required' => 'Pilih kejadian bencana terkait.',
            'edit_name.required'              => 'Nama posko wajib diisi.',
            'edit_name.max'                   => 'Nama posko maksimal 150 karakter.',
            'edit_address.required'           => 'Alamat posko wajib diisi.',
            'edit_lat.required'               => 'Titik koordinat latitude wajib ditentukan.',
            'edit_lng.required'               => 'Titik koordinat longitude wajib ditentukan.',
            'edit_capacity.required'          => 'Kapasitas daya tampung posko wajib diisi.',
            'edit_capacity.min'               => "Kapasitas posko tidak boleh lebih kecil dari jumlah pengungsi aktif saat ini ({$shelter->occupancy} jiwa).",
            'edit_contact.required'           => 'Nomor kontak narahubung posko wajib diisi.',
        ]);

        $newCap = (int) $this->edit_capacity;
        $newStatus = $shelter->status;

        // Penyesuaian otomatis status kapasitas jika tidak ditutup
        if ($shelter->status !== 'ditutup') {
            if ($shelter->occupancy >= $newCap) {
                $newStatus = 'penuh';
            } elseif ($shelter->status === 'penuh' && $shelter->occupancy < $newCap) {
                $newStatus = 'aktif';
            }
        }

        $shelter->update([
            'disaster_event_id' => $this->edit_disaster_event_id,
            'name'              => trim($this->edit_name),
            'address'           => trim($this->edit_address),
            'lat'               => (float) $this->edit_lat,
            'lng'               => (float) $this->edit_lng,
            'capacity'          => $newCap,
            'contact'           => trim($this->edit_contact),
            'status'            => $newStatus,
        ]);

        $this->closeEditModal();
        $this->dispatch('shelter-updated');

        $this->notification = [
            'type'    => 'success',
            'message' => "Data posko '{$shelter->name}' berhasil diperbarui.",
        ];
    }

    /**
     * Buka modal pembaruan okupansi pengungsi realtime.
     */
    public function openOccupancyModal(int $shelterId): void
    {
        $user = auth()->user();
        $query = Shelter::query();
        if (! $user->isAdmin()) {
            $query->where('coordinator_id', $user->id);
        }

        $shelter = $query->findOrFail($shelterId);

        $this->selectedShelterId = $shelter->id;
        $this->selectedShelterName = $shelter->name;
        $this->selectedShelterCapacity = $shelter->capacity;
        $this->newOccupancy = $shelter->occupancy;
        $this->showOccupancyModal = true;
    }

    /**
     * Tutup modal okupansi.
     */
    public function closeOccupancyModal(): void
    {
        $this->showOccupancyModal = false;
        $this->selectedShelterId = null;
        $this->selectedShelterName = '';
        $this->selectedShelterCapacity = 0;
        $this->newOccupancy = 0;
        $this->resetValidation();
    }

    /**
     * Simpan pembaruan jumlah pengungsi aktif dan sinkronkan status kapasitas posko.
     */
    public function updateOccupancy(): void
    {
        $this->validate([
            'newOccupancy' => 'required|numeric|min:0',
        ], [
            'newOccupancy.required' => 'Jumlah pengungsi aktif wajib diisi.',
            'newOccupancy.numeric'  => 'Jumlah pengungsi harus berupa angka valid.',
            'newOccupancy.min'      => 'Jumlah pengungsi tidak boleh kurang dari 0.',
        ]);

        $user = auth()->user();
        $query = Shelter::query();
        if (! $user->isAdmin()) {
            $query->where('coordinator_id', $user->id);
        }

        $shelter = $query->findOrFail($this->selectedShelterId);
        $occupancyVal = (int) $this->newOccupancy;

        // Aturan sinkronisasi status:
        // Jika posko tidak dalam status 'ditutup', sesuaikan status aktif/penuh secara otomatis
        $newStatus = $shelter->status;
        if ($shelter->status !== 'ditutup') {
            if ($occupancyVal >= $shelter->capacity) {
                $newStatus = 'penuh';
            } elseif ($shelter->status === 'penuh' && $occupancyVal < $shelter->capacity) {
                $newStatus = 'aktif';
            }
        }

        $shelter->update([
            'occupancy' => $occupancyVal,
            'status'    => $newStatus,
        ]);

        $this->closeOccupancyModal();
        $this->dispatch('shelter-updated');

        $this->notification = [
            'type'    => 'success',
            'message' => "Okupansi posko '{$shelter->name}' berhasil diperbarui menjadi {$shelter->occupancy} jiwa ({$shelter->occupancy_percentage}% - {$shelter->capacity_status_label}).",
        ];
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

        // Filter status kapasitas posko
        if (! empty($this->capacityFilter)) {
            $query->capacityStatus($this->capacityFilter);
        }

        // Filter kejadian bencana
        if (! empty($this->eventFilter)) {
            $query->where('disaster_event_id', $this->eventFilter);
        }

        // Kalkulasi ringkasan metrik kapasitas untuk kartu widget
        $statsQuery = Shelter::query();
        if (! $user->isAdmin()) {
            $statsQuery->where('coordinator_id', $user->id);
        }

        $allShelters = $statsQuery->get();
        $stats = [
            'total'              => $allShelters->count(),
            'tersedia'           => $allShelters->filter(fn ($s) => $s->capacity_status === 'tersedia')->count(),
            'hampir_penuh'       => $allShelters->filter(fn ($s) => $s->capacity_status === 'hampir_penuh')->count(),
            'penuh'              => $allShelters->filter(fn ($s) => $s->capacity_status === 'penuh')->count(),
            'ditutup'            => $allShelters->filter(fn ($s) => $s->capacity_status === 'ditutup')->count(),
            'total_capacity'     => $allShelters->sum('capacity'),
            'total_occupancy'    => $allShelters->sum('occupancy'),
            'total_remaining'    => $allShelters->sum('remaining_capacity'),
            'overall_percentage' => $allShelters->sum('capacity') > 0
                ? round(($allShelters->sum('occupancy') / $allShelters->sum('capacity')) * 100, 1)
                : 0.0,
        ];

        $shelters = $query->latest()->paginate(10);
        $activeEvents = DisasterEvent::active()->orderBy('name')->get();

        return view('livewire.admin.shelter-management', [
            'shelters'     => $shelters,
            'activeEvents' => $activeEvents,
            'stats'        => $stats,
        ]);
    }
}
