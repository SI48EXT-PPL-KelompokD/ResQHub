@component('layouts.admin')
    <div class="max-w-6xl mx-auto space-y-6">
        <div>
            <p class="text-sm font-medium text-blue-600">Katalog Barang Bantuan</p>
            <h1 class="text-2xl font-bold text-slate-900">Tambah Barang</h1>
        </div>

        @if (session('success'))
            <div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="grid items-start gap-6 lg:grid-cols-3">
            <form method="POST" action="{{ route('admin.items.store') }}" class="lg:col-span-1 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                <div class="space-y-5">
                <div class="space-y-1.5">
                    <label for="name" class="block text-sm font-semibold text-slate-700">Nama barang</label>
                    <input id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Contoh: Beras Premium" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label for="category" class="block text-sm font-semibold text-slate-700">Kategori</label>
                    <select id="category" name="category" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        @foreach(['pangan'=>'Pangan','sandang'=>'Sandang','kebersihan'=>'Kebersihan','obat_obatan'=>'Obat-obatan','perlengkapan_bayi'=>'Perlengkapan bayi','peralatan'=>'Peralatan'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label for="unit" class="block text-sm font-semibold text-slate-700">Satuan</label>
                    <input id="unit" name="unit" value="{{ old('unit') }}" required placeholder="Contoh: kg, dus, pcs" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @error('unit')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label for="min_threshold" class="block text-sm font-semibold text-slate-700">Ambang menipis bawaan</label>
                    <input id="min_threshold" type="number" min="0" name="min_threshold" value="{{ old('min_threshold', 10) }}" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @error('min_threshold')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                </div>
                <button class="mt-6 w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200">Simpan barang</button>
            </form>

            <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
                <h2 class="mb-4 font-semibold text-slate-900">Daftar katalog</h2>
                <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr class="border-b text-slate-500"><th class="pb-3">Nama</th><th class="pb-3">Kategori</th><th class="pb-3">Satuan</th><th class="pb-3">Ambang</th></tr></thead><tbody>
                    @forelse($items as $item)<tr class="border-b last:border-0"><td class="py-3 font-medium">{{ $item->name }}</td><td class="py-3">{{ str_replace('_', '-', $item->category) }}</td><td class="py-3">{{ $item->unit }}</td><td class="py-3">{{ $item->min_threshold }}</td></tr>@empty<tr><td colspan="4" class="py-6 text-center text-slate-500">Belum ada barang.</td></tr>@endforelse
                </tbody></table></div>
            </div>
        </div>
    </div>
@endcomponent
