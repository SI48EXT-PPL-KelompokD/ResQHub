@component('layouts.admin')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><p class="text-sm font-medium text-blue-600">Modul D · Kebutuhan</p><h1 class="text-2xl font-bold text-slate-900">Katalog Barang Bantuan</h1></div>
            <a href="{{ route('admin.items.create') }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">+ Tambah barang</a>
        </div>
        <form method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_220px_auto]">
            <input name="search" value="{{ request('search') }}" placeholder="Cari nama barang..." class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <select name="category" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100"><option value="">Semua kategori</option>@foreach(['pangan'=>'Pangan','sandang'=>'Sandang','kebersihan'=>'Kebersihan','obat_obatan'=>'Obat-obatan','perlengkapan_bayi'=>'Perlengkapan bayi','peralatan'=>'Peralatan'] as $value => $label)<option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>@endforeach</select>
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Terapkan</button>
        </form>
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Nama</th><th class="px-5 py-3">Kategori</th><th class="px-5 py-3">Satuan</th><th class="px-5 py-3">Ambang menipis</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Pemakaian</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($items as $item)<tr><td class="px-5 py-4 font-semibold text-slate-800">{{ $item->name }}</td><td class="px-5 py-4">{{ ucwords(str_replace('_', ' ', $item->category)) }}</td><td class="px-5 py-4">{{ $item->unit }}</td><td class="px-5 py-4">{{ $item->min_threshold }}</td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $item->is_active ? 'Aktif' : 'Tidak aktif' }}</span></td><td class="px-5 py-4 text-xs text-slate-600"><div>Kebutuhan posko: <b>{{ $item->aid_needs_count }}</b></div><div>Paket: <b>{{ $item->aid_package_items_count }}</b></div><div>Stok: <b>{{ $item->stocks_sum_quantity ?? 0 }}</b></div></td></tr>@empty<tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">Belum ada barang yang sesuai.</td></tr>@endforelse</tbody></table></div>
        <div>{{ $items->links() }}</div>
    </div>
@endcomponent
