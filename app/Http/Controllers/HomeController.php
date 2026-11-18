<?php

namespace App\Http\Controllers;

use App\Models\AidNeed;
use App\Models\DisasterEvent;
use App\Models\Shelter;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Beranda publik: seluruh angka berasal dari data operasional.
     */
    public function index(): View
    {
        $shelters = Shelter::query()->operational()->get();

        $openNeeds = AidNeed::query()->where('status', 'terbuka');

        return view('home', [
            'activeEvent' => DisasterEvent::query()->active()->latest('started_at')->first(),
            'stats' => [
                'events' => DisasterEvent::query()->active()->count(),
                'shelters' => $shelters->count(),
                'remaining' => $shelters->sum('remaining_capacity'),
                'needs' => (clone $openNeeds)->count(),
            ],
            'mapPoints' => $shelters->map(fn (Shelter $shelter) => [
                'name' => $shelter->name,
                'lat' => $shelter->lat,
                'lng' => $shelter->lng,
                'status' => $shelter->capacity_status,
                'statusLabel' => $this->capacityLabel($shelter),
                'remaining' => $shelter->remaining_capacity,
            ])->values()->all(),
            'shelters' => $shelters->sortByDesc('remaining_capacity')->take(5)->values(),
            'urgentNeeds' => (clone $openNeeds)
                ->with(['item', 'shelter'])
                ->orderByRaw("CASE WHEN priority = 'mendesak' THEN 1 WHEN priority = 'tinggi' THEN 2 ELSE 3 END")
                ->orderBy('deadline')
                ->take(5)
                ->get(),
        ]);
    }

    private function capacityLabel(Shelter $shelter): string
    {
        return match ($shelter->capacity_status) {
            'penuh' => 'Penuh',
            'hampir_penuh' => 'Hampir penuh',
            default => 'Tersedia',
        };
    }
}
