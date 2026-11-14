<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Shelter;
use App\Models\DisasterEvent;
use Illuminate\Database\Eloquent\Builder;

class ShelterSearch extends Component
{
    // Titik lokasi acuan pengguna (default: Kota Bandung / Jawa Barat)
    public float $userLat = -6.917464;
    public float $userLng = 107.619123;
    public string $userLocationName = 'Bandung (Pusat)';
    public bool $isGpsActive = false;

    // Filter & Search
    public string $search = '';
    public string $capacityFilter = ''; // 'tersedia', 'hampir_penuh', 'penuh', 'ditutup'
    public string $disasterEventFilter = '';
    public string $radiusFilter = ''; // dalam km, misal: 5, 10, 25, 50, atau '' (semua)
    public string $sortBy = 'distance'; // 'distance', 'capacity', 'name'
    public ?int $selectedShelterId = null;

    protected $queryString = [
        'search'              => ['except' => ''],
        'capacityFilter'      => ['except' => ''],
        'disasterEventFilter' => ['except' => ''],
        'radiusFilter'        => ['except' => ''],
        'sortBy'              => ['except' => 'distance'],
    ];

    public function mount(): void
    {
        // Jika ada kejadian bencana aktif, defaultkan ke koordinat bencana aktif pertama
        $activeEvent = DisasterEvent::active()->first();
        if ($activeEvent && $activeEvent->latitude && $activeEvent->longitude) {
            $this->userLat = (float) $activeEvent->latitude;
            $this->userLng = (float) $activeEvent->longitude;
            $this->userLocationName = $activeEvent->name . ' (' . $activeEvent->regency . ')';
        }
    }

    public function setUserLocation(float $lat, float $lng, string $locationName = ''): void
    {
        $this->userLat = $lat;
        $this->userLng = $lng;
        $this->userLocationName = $locationName ?: sprintf('%.4f, %.4f', $lat, $lng);
        $this->isGpsActive = true;

        $this->dispatch('user-location-updated', [
            'lat'  => $this->userLat,
            'lng'  => $this->userLng,
            'name' => $this->userLocationName,
        ]);
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->capacityFilter = '';
        $this->disasterEventFilter = '';
        $this->radiusFilter = '';
        $this->sortBy = 'distance';
        $this->selectedShelterId = null;
    }

    public function selectShelter(int $shelterId): void
    {
        $this->selectedShelterId = ($this->selectedShelterId === $shelterId) ? null : $shelterId;
        if ($this->selectedShelterId) {
            $shelter = Shelter::find($this->selectedShelterId);
            if ($shelter) {
                $this->dispatch('focus-shelter-marker', [
                    'id'   => $shelter->id,
                    'lat'  => (float) $shelter->lat,
                    'lng'  => (float) $shelter->lng,
                    'name' => $shelter->name,
                ]);
            }
        }
    }

    public function render()
    {
        $query = Shelter::query()->with(['disasterEvent', 'coordinator']);

        // Filter status operasional posko
        if ($this->capacityFilter === 'ditutup') {
            $query->where('status', 'ditutup');
        } else {
            $query->operational();
            if ($this->capacityFilter !== '') {
                $query->capacityStatus($this->capacityFilter);
            }
        }

        // Filter kejadian bencana
        if ($this->disasterEventFilter !== '') {
            $query->where('disaster_event_id', $this->disasterEventFilter);
        }

        // Filter pencarian teks nama posko atau alamat atau bencana
        if (trim($this->search) !== '') {
            $s = '%' . trim($this->search) . '%';
            $query->where(function (Builder $b) use ($s) {
                $b->where('name', 'like', $s)
                  ->orWhere('address', 'like', $s)
                  ->orWhereHas('disasterEvent', function (Builder $eb) use ($s) {
                      $eb->where('name', 'like', $s)
                         ->orWhere('regency', 'like', $s);
                  });
            });
        }

        // Kalkulasi jarak Haversine dan radius
        $radiusVal = ($this->radiusFilter !== '' && is_numeric($this->radiusFilter)) ? (float) $this->radiusFilter : null;
        $query->nearby($this->userLat, $this->userLng, $radiusVal);

        if ($this->sortBy === 'capacity') {
            $query->orderByRaw('(capacity - occupancy) DESC');
        } elseif ($this->sortBy === 'name') {
            $query->orderBy('name', 'ASC');
        }

        $shelters = $query->get();

        // Data statistik agregat untuk widget KPI informasi publik
        $allOperational = Shelter::operational()->get();
        $stats = [
            'total_shelters'    => $allOperational->count(),
            'total_capacity'    => $allOperational->sum('capacity'),
            'total_occupancy'   => $allOperational->sum('occupancy'),
            'total_available'   => $allOperational->filter(fn ($s) => $s->capacity_status === 'tersedia')->count(),
            'total_nearly_full' => $allOperational->filter(fn ($s) => $s->capacity_status === 'hampir_penuh')->count(),
            'total_full'        => $allOperational->filter(fn ($s) => $s->capacity_status === 'penuh')->count(),
        ];

        // Format data marker untuk peta Leaflet
        $mapMarkers = $shelters->map(function ($s) {
            return [
                'id'                 => $s->id,
                'name'               => $s->name,
                'address'            => $s->address,
                'lat'                => (float) $s->lat,
                'lng'                => (float) $s->lng,
                'capacity'           => (int) $s->capacity,
                'occupancy'          => (int) $s->occupancy,
                'remaining'          => $s->remaining_capacity,
                'percentage'         => $s->occupancy_percentage,
                'capacity_status'    => $s->capacity_status,
                'status_label'       => $s->capacity_status_label,
                'badge_classes'      => $s->capacity_badge_classes,
                'contact'            => $s->contact,
                'distance'           => round((float) ($s->distance ?? 0), 2),
                'formatted_distance' => $s->formatted_distance,
                'google_maps_url'    => $s->google_maps_url,
                'event_name'         => $s->disasterEvent?->name ?? 'Kejadian Bencana',
            ];
        });

        $activeEvents = DisasterEvent::active()->orderBy('name')->get();

        return view('livewire.public.shelter-search', [
            'shelters'     => $shelters,
            'stats'        => $stats,
            'mapMarkers'   => $mapMarkers,
            'activeEvents' => $activeEvents,
        ])->layout('layouts.public');
    }
}
