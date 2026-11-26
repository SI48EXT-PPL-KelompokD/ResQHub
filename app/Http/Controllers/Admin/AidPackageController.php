<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAidPackageRequest;
use App\Http\Requests\UpdateAidPackageRequest;
use App\Models\AidPackage;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AidPackageController extends Controller
{
    private const TARGETS = [
        'keluarga' => 'Keluarga',
        'jiwa' => 'Jiwa',
        'balita' => 'Balita',
        'lansia' => 'Lansia',
    ];

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', AidPackage::class);

        $packages = AidPackage::query()
            ->search($request->string('search')->toString())
            ->when($request->filled('target'), fn ($query) => $query->where('target', $request->string('target')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'aktif'))
            ->with(['packageItems.item', 'createdBy'])
            ->withCount('distributions')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.packages.index', [
            'packages' => $packages,
            'targets' => self::TARGETS,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', AidPackage::class);

        return view('admin.packages.create', [
            'items' => Item::query()->active()->orderBy('name')->get(),
            'targets' => self::TARGETS,
        ]);
    }

    public function store(StoreAidPackageRequest $request)
    {
        Gate::authorize('create', AidPackage::class);

        DB::transaction(function () use ($request): void {
            $package = AidPackage::create([
                ...$request->safe()->only(['name', 'target', 'description']),
                'created_by' => $request->user()->id,
            ]);
            $this->replaceItems($package, $request->validated('items'));
        });

        return redirect()->route('admin.packages.index')->with('success', 'Paket bantuan berhasil dibuat.');
    }

    public function show(Request $request, AidPackage $aidPackage): View
    {
        Gate::authorize('view', $aidPackage);

        $targetCount = max(1, $request->integer('target_count', 1));
        $aidPackage->load(['packageItems.item', 'createdBy'])->loadCount('distributions');

        return view('admin.packages.show', [
            'package' => $aidPackage,
            'targetCount' => $targetCount,
            'requirements' => $aidPackage->requirementsFor($targetCount),
        ]);
    }

    public function edit(AidPackage $aidPackage): View
    {
        Gate::authorize('update', $aidPackage);

        return view('admin.packages.edit', [
            'package' => $aidPackage->load(['packageItems.item']),
            'items' => Item::query()->active()->orderBy('name')->get(),
            'targets' => self::TARGETS,
            'isUsed' => $aidPackage->isUsed(),
        ]);
    }

    public function update(UpdateAidPackageRequest $request, AidPackage $aidPackage)
    {
        Gate::authorize('update', $aidPackage);

        DB::transaction(function () use ($request, $aidPackage): void {
            $aidPackage->update($request->safe()->only(['name', 'target', 'description']));

            if (! $aidPackage->isUsed()) {
                $this->replaceItems($aidPackage, $request->validated('items'));
            }
        });

        return redirect()->route('admin.packages.index')->with('success', 'Paket bantuan berhasil diperbarui.');
    }

    public function destroy(AidPackage $aidPackage)
    {
        Gate::authorize('delete', $aidPackage);

        if ($aidPackage->isUsed()) {
            $aidPackage->update(['is_active' => false]);
            $message = 'Paket sudah pernah disalurkan, sehingga dinonaktifkan dan tetap tercatat dalam riwayat.';
        } else {
            $aidPackage->delete();
            $message = 'Paket bantuan berhasil dihapus.';
        }

        return redirect()->route('admin.packages.index')->with('success', $message);
    }

    /** @param array<int, array{item_id: int, quantity: int}> $items */
    private function replaceItems(AidPackage $package, array $items): void
    {
        $package->packageItems()->delete();
        $package->packageItems()->createMany($items);
    }
}
