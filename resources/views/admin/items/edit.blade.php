@component('layouts.admin')
    <div class="mx-auto max-w-2xl space-y-6">
        <div>
            <p class="text-sm font-medium text-blue-600">Katalog Barang Bantuan</p>
            <h1 class="text-2xl font-bold text-slate-900">Edit Barang</h1>
        </div>

        <form method="POST" action="{{ route('admin.items.update', $item) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')
            <div class="space-y-5">
                <div><label for="name" class="block text-sm font-semibold text-slate-700">Nama barang</label><input id="name" name="name" value="{{ old('name', $item->name) }}" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">@error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div><label for="category" class="block text-sm font-semibold text-slate-700">Kategori</label><select id="category" name="category" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">@foreach(['pangan'=>'Pangan','sandang'=>'Sandang','kebersihan'=>'Kebersihan','obat_obatan'=>'Obat-obatan','perlengkapan_bayi'=>'Perlengkapan bayi','peralatan'=>'Peralatan'] as $value => $label)<option value="{{ $value }}" @selected(old('category', $item->category) === $value)>{{ $label }}</option>@endforeach</select>@error('category')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div><label for="unit" class="block text-sm font-semibold text-slate-700">Satuan</label>@if($item->stocks()->exists())<input type="hidden" name="unit" value="{{ $item->unit }}">@endif<input id="unit" name="unit" value="{{ old('unit', $item->unit) }}" required @disabled($item->stocks()->exists()) class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm disabled:bg-slate-100">@if($item->stocks()->exists())<p class="mt-1 text-xs text-amber-700">Satuan terkunci karena barang sudah memiliki stok.</p>@endif @error('unit')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div><label for="min_threshold" class="block text-sm font-semibold text-slate-700">Ambang menipis bawaan</label><input id="min_threshold" type="number" min="0" name="min_threshold" value="{{ old('min_threshold', $item->min_threshold) }}" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">@error('min_threshold')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            </div>
            <div class="mt-6 flex gap-3"><a href="{{ route('admin.items.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Batal</a><button class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Simpan perubahan</button></div>
        </form>
    </div>
@endcomponent
