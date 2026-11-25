<?php

namespace App\Livewire\Admin;

use App\Models\DisasterReport;
use App\Models\GoodsDonation;
use App\Models\Shelter;
use App\Models\ShelterFacility;
use App\Models\User;
use App\Services\HealthCheckService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        /** @var User $user */
        $user = Auth::user();

        $sections = match ($user->role) {
            'admin' => $this->adminView($user),
            'koordinator' => $this->coordinatorView($user),
            'nakes' => $this->medicView($user),
            'relawan' => $this->volunteerView($user),
            'donatur' => $this->donorView($user),
            default => $this->citizenView($user),
        };

        return view('livewire.admin.dashboard', $sections + [
            'activeEvent' => DB::table('disaster_events')->where('status', 'aktif')->orderByDesc('started_at')->first(),
        ]);
    }

    private function adminView(User $user): array
    {
        $occupancy = $this->occupancy();

        return [
            'metrics' => [
                $this->metric('Laporan menunggu', DisasterReport::query()->pending()->count(), 'perlu diverifikasi'),
                $this->metric('SOS baru', DB::table('sos_requests')->where('status', 'baru')->count(), 'belum ditangani'),
                $this->metric('Okupansi posko', $occupancy['label'], $occupancy['hint'], route('admin.shelters')),
                $this->metric('Stok menipis', $this->lowStockQuery()->count(), 'di bawah ambang batas', route('admin.stocks.index')),
            ],
            'pendingReports' => DisasterReport::query()->pending()->with('reporter')->latest()->take(5)->get(),
            'recentSos' => $this->recentSos(),
            'urgentNeeds' => $this->urgentNeeds(),
            'lowStocks' => $this->lowStocks(),
            'triageSummary' => app(HealthCheckService::class)->getDailyTriageSummary($user),
            'mapPoints' => $this->mapPoints(),
        ];
    }

    private function coordinatorView(User $user): array
    {
        $shelterIds = Shelter::query()->where('coordinator_id', $user->id)->pluck('id')->all();
        $occupancy = $this->occupancy($shelterIds);

        $vulnerable = (int) DB::table('refugees')
            ->whereNull('deleted_at')
            ->where('status', 'di_posko')
            ->whereIn('shelter_id', $shelterIds)
            ->selectRaw('COALESCE(SUM(infants + elderly + pregnant + disabled), 0) as total')
            ->value('total');

        return [
            'metrics' => [
                $this->metric('Okupansi posko saya', $occupancy['label'], $occupancy['hint'], route('admin.shelters')),
                $this->metric('Kelompok rentan', $vulnerable, 'balita, lansia, ibu hamil, disabilitas', route('admin.refugees')),
                $this->metric('Kebutuhan terbuka', DB::table('aid_needs')->whereNull('deleted_at')->where('status', 'terbuka')->whereIn('shelter_id', $shelterIds)->count(), 'di posko saya', route('admin.needs.index')),
                $this->metric('Stok menipis', $this->lowStockQuery($shelterIds)->count(), 'di posko saya', route('admin.stocks.index')),
            ],
            'recentSos' => $this->recentSos(),
            'urgentNeeds' => $this->urgentNeeds($shelterIds),
            'lowStocks' => $this->lowStocks($shelterIds),
            'brokenFacilities' => $this->brokenFacilities($shelterIds),
            'triageSummary' => app(HealthCheckService::class)->getDailyTriageSummary($user),
            'mapPoints' => $this->mapPoints($shelterIds),
        ];
    }

    /**
     * Fasilitas berkondisi "Tidak Berfungsi" pada posko kelolaan koordinator (PBI-05).
     *
     * @param  list<int>  $shelterIds
     */
    private function brokenFacilities(array $shelterIds): Collection
    {
        return ShelterFacility::query()
            ->with('shelter:id,name')
            ->broken()
            ->whereIn('shelter_id', $shelterIds)
            ->latest('updated_at')
            ->limit(5)
            ->get();
    }

    private function medicView(User $user): array
    {
        $summary = app(HealthCheckService::class)->getDailyTriageSummary($user);
        $tasks = $this->taskCounts($user);

        return [
            'metrics' => [
                $this->metric('Pemeriksaan hari ini', $summary['totals']['total'] ?? 0, 'tercatat', route('admin.health-checks')),
                $this->metric('Triase merah', $summary['unreferred_merah'] ?? 0, 'belum dirujuk', route('admin.health-checks')),
                $this->metric('Tawaran tugas', $tasks['ditawarkan'], 'menunggu jawaban'),
                $this->metric('Tugas berjalan', $tasks['diterima'], 'sudah diterima'),
            ],
            'triageSummary' => $summary,
            'myTasks' => $this->myTasks($user),
        ];
    }

    private function volunteerView(User $user): array
    {
        $tasks = $this->taskCounts($user);

        return [
            'metrics' => [
                $this->metric('Tawaran tugas', $tasks['ditawarkan'], 'menunggu jawaban', route('admin.tasks')),
                $this->metric('Tugas berjalan', $tasks['diterima'], 'sudah diterima', route('admin.tasks')),
                $this->metric('Tugas selesai', $tasks['selesai'], 'sepanjang waktu', route('admin.tasks')),
                $this->metric('Posko beroperasi', $this->operationalShelters(), 'saat ini', route('public.shelters')),
            ],
            'myTasks' => $this->myTasks($user),
            'mapPoints' => $this->mapPoints(),
        ];
    }

    private function citizenView(User $user): array
    {
        $counts = DisasterReport::query()->ownedBy($user->id)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'metrics' => [
                $this->metric('Laporan menunggu', $counts[DisasterReport::STATUS_PENDING] ?? 0, 'sedang diperiksa', route('admin.reports.index')),
                $this->metric('Laporan diterima', $counts[DisasterReport::STATUS_ACCEPTED] ?? 0, 'menjadi kejadian resmi', route('admin.reports.index')),
                $this->metric('Laporan ditolak', $counts[DisasterReport::STATUS_REJECTED] ?? 0, 'lihat alasannya', route('admin.reports.index')),
                $this->metric('Posko beroperasi', $this->operationalShelters(), 'saat ini', route('public.shelters')),
            ],
            'myReports' => DisasterReport::query()->ownedBy($user->id)->latest()->take(5)->get(),
            'mapPoints' => $this->mapPoints(),
        ];
    }

    private function donorView(User $user): array
    {
        $counts = GoodsDonation::query()->ownedBy($user)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'metrics' => [
                $this->metric('Janji donasi aktif', $counts['dijanjikan'] ?? 0, 'menunggu dikirim', route('donor.goods-donations.index')),
                $this->metric('Sudah diterima', ($counts['diterima'] ?? 0) + ($counts['disalurkan'] ?? 0), 'oleh posko', route('donor.goods-donations.index')),
                $this->metric('Kebutuhan terbuka', DB::table('aid_needs')->whereNull('deleted_at')->where('status', 'terbuka')->count(), 'di seluruh posko', route('needs.public')),
                $this->metric('Posko beroperasi', $this->operationalShelters(), 'saat ini', route('public.shelters')),
            ],
            'urgentNeeds' => $this->urgentNeeds(),
            'myGoodsDonations' => GoodsDonation::withDisplay()->ownedBy($user)->latest('goods_donations.id')->take(4)->get(),
        ];
    }

    private function metric(string $label, int|string $value, string $hint, ?string $href = null): array
    {
        return compact('label', 'value', 'hint', 'href');
    }

    /**
     * @param  list<int>|null  $shelterIds
     * @return array{label: string, hint: string}
     */
    private function occupancy(?array $shelterIds = null): array
    {
        $totals = DB::table('shelters')
            ->where('status', '!=', 'ditutup')
            ->when($shelterIds !== null, fn ($query) => $query->whereIn('id', $shelterIds))
            ->selectRaw('COALESCE(SUM(capacity), 0) as capacity, COALESCE(SUM(occupancy), 0) as occupancy')
            ->first();

        $percent = $totals->capacity > 0 ? (int) round(($totals->occupancy / $totals->capacity) * 100) : 0;

        return [
            'label' => number_format($totals->occupancy, 0, ',', '.').' / '.number_format($totals->capacity, 0, ',', '.'),
            'hint' => "jiwa, {$percent}% terisi",
        ];
    }

    private function operationalShelters(): int
    {
        return Shelter::query()->operational()->count();
    }

    /** @param  list<int>|null  $shelterIds */
    private function lowStockQuery(?array $shelterIds = null)
    {
        return DB::table('stocks')
            ->whereNull('stocks.deleted_at')
            ->whereColumn('stocks.quantity', '<', 'stocks.min_threshold')
            ->when($shelterIds !== null, fn ($query) => $query->whereIn('stocks.shelter_id', $shelterIds));
    }

    /** @param  list<int>|null  $shelterIds */
    private function lowStocks(?array $shelterIds = null): Collection
    {
        return $this->lowStockQuery($shelterIds)
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->join('shelters', 'stocks.shelter_id', '=', 'shelters.id')
            ->select('stocks.*', 'items.name as item_name', 'items.unit', 'shelters.name as shelter_name')
            ->orderByRaw('stocks.quantity * 1.0 / NULLIF(stocks.min_threshold, 0)')
            ->take(5)
            ->get();
    }

    private function recentSos(): Collection
    {
        return DB::table('sos_requests')
            ->join('users', 'sos_requests.requester_id', '=', 'users.id')
            ->select('sos_requests.*', 'users.name as requester_name')
            ->orderByRaw("CASE WHEN sos_requests.status = 'baru' THEN 1 WHEN sos_requests.status = 'ditangani' THEN 2 ELSE 3 END")
            ->orderByDesc('sos_requests.created_at')
            ->take(5)
            ->get();
    }

    /** @param  list<int>|null  $shelterIds */
    private function urgentNeeds(?array $shelterIds = null): Collection
    {
        return DB::table('aid_needs')
            ->join('items', 'aid_needs.item_id', '=', 'items.id')
            ->join('shelters', 'aid_needs.shelter_id', '=', 'shelters.id')
            ->whereNull('aid_needs.deleted_at')
            ->where('aid_needs.status', 'terbuka')
            ->when($shelterIds !== null, fn ($query) => $query->whereIn('aid_needs.shelter_id', $shelterIds))
            ->select('aid_needs.*', 'items.name as item_name', 'items.unit', 'shelters.name as shelter_name')
            ->orderByRaw("CASE WHEN aid_needs.priority = 'mendesak' THEN 1 WHEN aid_needs.priority = 'tinggi' THEN 2 ELSE 3 END")
            ->orderBy('aid_needs.deadline')
            ->take(5)
            ->get();
    }

    /** @return array{ditawarkan: int, diterima: int, selesai: int} */
    private function taskCounts(User $user): array
    {
        $rows = DB::table('task_assignments')
            ->where('volunteer_profile_id', $user->volunteerProfile?->id ?? 0)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'ditawarkan' => (int) ($rows['ditawarkan'] ?? 0),
            'diterima' => (int) ($rows['diterima'] ?? 0),
            'selesai' => (int) ($rows['selesai'] ?? 0),
        ];
    }

    private function myTasks(User $user): Collection
    {
        return DB::table('task_assignments')
            ->join('volunteer_tasks', 'volunteer_tasks.id', '=', 'task_assignments.volunteer_task_id')
            ->where('task_assignments.volunteer_profile_id', $user->volunteerProfile?->id ?? 0)
            ->whereIn('task_assignments.status', ['ditawarkan', 'diterima'])
            ->whereIn('volunteer_tasks.status', ['terbuka', 'berjalan'])
            ->orderBy('volunteer_tasks.start_at')
            ->take(5)
            ->get(['volunteer_tasks.title', 'volunteer_tasks.start_at', 'task_assignments.status as assignment_status']);
    }

    /**
     * @param  list<int>|null  $shelterIds
     * @return list<array<string, mixed>>
     */
    private function mapPoints(?array $shelterIds = null): array
    {
        return Shelter::query()
            ->operational()
            ->when($shelterIds !== null, fn ($query) => $query->whereIn('id', $shelterIds))
            ->get()
            ->map(fn (Shelter $shelter) => [
                'name' => $shelter->name,
                'lat' => $shelter->lat,
                'lng' => $shelter->lng,
                'status' => $shelter->capacity_status,
                'statusLabel' => ['penuh' => 'Penuh', 'hampir_penuh' => 'Hampir penuh'][$shelter->capacity_status] ?? 'Tersedia',
                'remaining' => $shelter->remaining_capacity,
            ])
            ->values()
            ->all();
    }
}
