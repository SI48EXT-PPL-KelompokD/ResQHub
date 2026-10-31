<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Item;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(\Illuminate\Http\Request $request): View
    {
        \Illuminate\Support\Facades\Gate::authorize('viewAny', Item::class);

        $items = Item::query()
            ->search($request->string('search')->toString())
            ->category($request->string('category')->toString())
            ->withCount(['aidNeeds', 'aidPackageItems'])
            ->withSum('stocks', 'quantity')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.items.index', compact('items'));
    }

    public function create(): View
    {
        Gate::authorize('create', Item::class);

        return view('admin.items.create', [
            'items' => Item::latest()->get(),
        ]);
    }

    public function store(StoreItemRequest $request)
    {
        Item::create($request->validated());

        return redirect()->route('admin.items.create')->with('success', 'Barang berhasil ditambahkan ke katalog.');
    }

    public function edit(Item $item): View
    {
        Gate::authorize('update', $item);

        return view('admin.items.edit', compact('item'));
    }

    public function update(UpdateItemRequest $request, Item $item)
    {
        $item->update($request->validated());

        return redirect()->route('admin.items.index')->with('success', 'Barang berhasil diperbarui.');
    }
}
