<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.admin')]
#[Title('Dashboard Pusdalops')]
class Dashboard extends Component
{
    public function render()
    {
        // 1. Data Ringkasan Metrik
        $totalCapacity = DB::table('shelters')->where('status', '!=', 'ditutup')->sum('capacity');
        $totalOccupancy = DB::table('shelters')->where('status', '!=', 'ditutup')->sum('occupancy');
        $occupancyPercent = $totalCapacity > 0 ? round(($totalOccupancy / $totalCapacity) * 100) : 0;

        $vulnerableCount = DB::table('refugees')
            ->whereNull('deleted_at')
            ->where('status', 'di_posko')
            ->selectRaw('SUM(infants + elderly + pregnant + disabled) as total')
            ->value('total') ?? 0;

        $newSosCount = DB::table('sos_requests')->where('status', 'baru')->count();

        $lowStockCount = DB::table('stocks')
            ->whereRaw('quantity <= min_threshold')
            ->count();

        // 2. Data SOS Masuk Terkini
        $recentSos = DB::table('sos_requests')
            ->join('users', 'sos_requests.requester_id', '=', 'users.id')
            ->select('sos_requests.*', 'users.name as requester_name')
            ->orderByRaw("CASE WHEN sos_requests.status = 'baru' THEN 1 WHEN sos_requests.status = 'ditangani' THEN 2 ELSE 3 END")
            ->orderBy('sos_requests.created_at', 'desc')
            ->take(5)
            ->get();

        // 3. Kebutuhan Mendesak
        $urgentNeeds = DB::table('aid_needs')
            ->join('items', 'aid_needs.item_id', '=', 'items.id')
            ->join('shelters', 'aid_needs.shelter_id', '=', 'shelters.id')
            ->whereNull('aid_needs.deleted_at')
            ->where('aid_needs.status', 'terbuka')
            ->select('aid_needs.*', 'items.name as item_name', 'items.unit', 'shelters.name as shelter_name')
            ->orderByRaw("CASE WHEN aid_needs.priority = 'mendesak' THEN 1 WHEN aid_needs.priority = 'tinggi' THEN 2 ELSE 3 END")
            ->take(4)
            ->get();

        // 4. Stok Menipis
        $lowStocks = DB::table('stocks')
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->join('shelters', 'stocks.shelter_id', '=', 'shelters.id')
            ->whereRaw('stocks.quantity <= stocks.min_threshold')
            ->select('stocks.*', 'items.name as item_name', 'items.unit', 'items.category', 'shelters.name as shelter_name')
            ->take(4)
            ->get();

        // 5. Kejadian Bencana Aktif Terkini
        $activeEvent = DB::table('disaster_events')
            ->where('status', 'aktif')
            ->first();

        return view('livewire.admin.dashboard', [
            'totalCapacity' => $totalCapacity,
            'totalOccupancy' => $totalOccupancy,
            'occupancyPercent' => $occupancyPercent,
            'vulnerableCount' => $vulnerableCount,
            'newSosCount' => $newSosCount,
            'lowStockCount' => $lowStockCount,
            'recentSos' => $recentSos,
            'urgentNeeds' => $urgentNeeds,
            'lowStocks' => $lowStocks,
            'activeEvent' => $activeEvent,
        ]);
    }
}
