<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Models\Item;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ItemController extends Controller
{
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
}
