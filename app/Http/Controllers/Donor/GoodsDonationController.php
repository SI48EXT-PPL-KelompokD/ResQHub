<?php

namespace App\Http\Controllers\Donor;

use App\Enums\DeliveryMethod;
use App\Http\Requests\CancelGoodsDonationRequest;
use App\Http\Requests\StoreGoodsDonationRequest;
use App\Http\Requests\UpdateGoodsDonationRequest;
use App\Models\ActivityLog;
use App\Models\AidNeed;
use App\Models\GoodsDonation;
use App\Services\GoodsDonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GoodsDonationController
{
    public function __construct(private GoodsDonationService $service) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', GoodsDonation::class);

        $donations = GoodsDonation::query()
            ->ownedBy($request->user())
            ->with('aidNeed')
            ->latest()
            ->paginate(10);

        return view('donor.goods-donations.index', compact('donations'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', GoodsDonation::class);

        $need = AidNeed::query()->where('status', 'terbuka')->findOrFail($request->integer('aid_need'));

        return view('donor.goods-donations.create', [
            'need' => $need,
            'remaining' => max(0, $need->qty_needed - $need->qty_pledged),
            'methods' => DeliveryMethod::cases(),
        ]);
    }

    public function store(StoreGoodsDonationRequest $request): RedirectResponse
    {
        $donation = $this->service->create($request->user(), $request->validated());

        return redirect()
            ->route('donor.goods-donations.show', $donation)
            ->with('success', "Janji donasi {$donation->code} berhasil dibuat.");
    }

    public function show(GoodsDonation $goodsDonation): View
    {
        Gate::authorize('view', $goodsDonation);

        $goodsDonation->load('aidNeed');

        $logs = ActivityLog::query()
            ->where('subject_type', GoodsDonation::class)
            ->where('subject_id', $goodsDonation->id)
            ->oldest('id')
            ->get();

        return view('donor.goods-donations.show', [
            'donation' => $goodsDonation,
            'logs' => $logs,
        ]);
    }

    public function edit(GoodsDonation $goodsDonation): View
    {
        Gate::authorize('update', $goodsDonation);

        $need = $goodsDonation->aidNeed;

        return view('donor.goods-donations.edit', [
            'donation' => $goodsDonation,
            'need' => $need,
            'remaining' => max(0, $need->qty_needed - $need->qty_pledged + $goodsDonation->quantity),
            'methods' => DeliveryMethod::cases(),
        ]);
    }

    public function update(UpdateGoodsDonationRequest $request, GoodsDonation $goodsDonation): RedirectResponse
    {
        $this->service->update($goodsDonation, $request->user(), $request->validated());

        return redirect()
            ->route('donor.goods-donations.show', $goodsDonation)
            ->with('success', 'Janji donasi berhasil diperbarui.');
    }

    public function destroy(CancelGoodsDonationRequest $request, GoodsDonation $goodsDonation): RedirectResponse
    {
        $this->service->cancel($goodsDonation, $request->user(), $request->validated('cancel_reason'));

        return redirect()
            ->route('donor.goods-donations.index')
            ->with('success', "Janji donasi {$goodsDonation->code} dibatalkan.");
    }
}
