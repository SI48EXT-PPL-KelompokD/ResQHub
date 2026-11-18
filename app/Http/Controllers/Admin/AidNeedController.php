<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAidNeedRequest;
use App\Http\Requests\UpdateAidNeedRequest;
use App\Models\AidNeed;
use App\Models\Item;
use App\Models\Shelter;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AidNeedController extends Controller
{
    private const PRIORITIES = [
        'mendesak' => 'Mendesak',
        'tinggi' => 'Tinggi',
        'normal' => 'Normal',
    ];

    private const CATEGORIES = [
        'pangan' => 'Pangan',
        'sandang' => 'Sandang',
        'kebersihan' => 'Kebersihan',
        'obat_obatan' => 'Obat-obatan',
        'perlengkapan_bayi' => 'Perlengkapan bayi',
        'peralatan' => 'Peralatan',
    ];

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', AidNeed::class);

        $needs = $this->filteredQuery($request)
            ->whereIn('shelter_id', $this->ownedShelterIds($request->user()))
            ->when(
                $request->filled('status'),
                fn (Builder $query) => $query->where('status', $request->string('status')->toString())
            )
            ->orderByRaw("CASE WHEN priority = 'mendesak' THEN 1 WHEN priority = 'tinggi' THEN 2 ELSE 3 END")
            ->orderBy('deadline')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.needs.index', [
            'needs' => $needs,
            'priorities' => self::PRIORITIES,
            'statuses' => [
                'terbuka' => 'Terbuka',
                'terpenuhi' => 'Terpenuhi',
                'ditutup' => 'Ditutup',
            ],
        ]);
    }

    public function publicIndex(Request $request): View
    {
        $needs = $this->filteredQuery($request)
            ->where('status', 'terbuka')
            ->orderByRaw("CASE WHEN priority = 'mendesak' THEN 1 WHEN priority = 'tinggi' THEN 2 ELSE 3 END")
            ->orderBy('deadline')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('needs.index', [
            'needs' => $needs,
            'events' => DB::table('disaster_events')->select('id', 'name')->orderBy('name')->get(),
            'priorities' => self::PRIORITIES,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', AidNeed::class);

        return view('admin.needs.create', [
            'shelters' => Shelter::query()
                ->where('coordinator_id', $request->user()->id)
                ->whereIn('status', ['aktif', 'penuh'])
                ->orderBy('name')
                ->get(),
            'items' => Item::query()->active()->orderBy('name')->get(),
            'priorities' => self::PRIORITIES,
        ]);
    }

    public function store(StoreAidNeedRequest $request)
    {
        Gate::authorize('create', AidNeed::class);

        AidNeed::create($request->validated());

        return redirect()
            ->route('admin.needs.index')
            ->with('success', 'Kebutuhan bantuan berhasil ditambahkan.');
    }

    public function edit(AidNeed $aidNeed): View
    {
        Gate::authorize('update', $aidNeed);

        return view('admin.needs.edit', [
            'aidNeed' => $aidNeed->load(['item', 'shelter']),
            'priorities' => self::PRIORITIES,
        ]);
    }

    public function update(UpdateAidNeedRequest $request, AidNeed $aidNeed)
    {
        Gate::authorize('update', $aidNeed);

        $aidNeed->update($request->validated());

        return redirect()
            ->route('admin.needs.index')
            ->with('success', 'Kebutuhan bantuan berhasil diperbarui.');
    }

    public function destroy(Request $request, AidNeed $aidNeed)
    {
        Gate::authorize('delete', $aidNeed);

        $validated = $request->validate([
            'close_reason' => ['required', 'string', 'max:255'],
        ]);

        $hasCommitments = $aidNeed->qty_pledged > 0 || $aidNeed->qty_fulfilled > 0;
        $aidNeed->update([
            'status' => 'ditutup',
            'close_reason' => $validated['close_reason'],
        ]);

        if ($hasCommitments) {
            return redirect()
                ->route('admin.needs.index')
                ->with('success', 'Kebutuhan yang sudah memiliki komitmen ditutup dan tetap disimpan.');
        }

        $aidNeed->delete();

        return redirect()
            ->route('admin.needs.index')
            ->with('success', 'Kebutuhan bantuan berhasil dihapus.');
    }

    /**
     * @return array<int, int>
     */
    private function ownedShelterIds(User $user): array
    {
        return Shelter::query()
            ->where('coordinator_id', $user->id)
            ->pluck('id')
            ->all();
    }

    private function filteredQuery(Request $request): Builder
    {
        return AidNeed::query()
            ->with(['item', 'shelter'])
            ->when(
                $request->filled('event_id'),
                fn (Builder $query) => $query->whereHas(
                    'shelter',
                    fn (Builder $shelters) => $shelters->where('disaster_event_id', $request->integer('event_id'))
                )
            )
            ->when(
                $request->filled('category'),
                fn (Builder $query) => $query->whereHas(
                    'item',
                    fn (Builder $items) => $items->where('category', $request->string('category')->toString())
                )
            )
            ->when(
                $request->filled('region'),
                fn (Builder $query) => $query->whereHas(
                    'shelter',
                    fn (Builder $shelters) => $shelters->where(
                        'address',
                        'like',
                        '%'.$request->string('region')->toString().'%'
                    )
                )
            )
            ->when(
                $request->filled('priority'),
                fn (Builder $query) => $query->where('priority', $request->string('priority')->toString())
            );
    }
}
