@component('layouts.admin')
    <div class="mx-auto max-w-7xl space-y-6">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-blue-700">Modul D · Kebutuhan bantuan</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Paket Bantuan Standar</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-600">Susun komposisi bantuan yang bisa dihitung ulang untuk kebutuhan posko.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.needs.index') }}" class="cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200">Kebutuhan posko</a>
                <a href="{{ route('admin.packages.create') }}" class="cursor-pointer rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200">Tambah paket</a>
            </div>
        </div>

        <form method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_180px_160px_auto]">
            <div>
                <label for="search" class="sr-only">Cari paket</label>
                <input id="search" name="search" value="{{ request('search') }}" placeholder="Cari nama paket..." class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </div>
            <div>
                <label for="target" class="sr-only">Target paket</label>
                <select id="target" name="target" class="block w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua target</option>
                    @foreach ($targets as $value => $label)
                        <option value="{{ $value }}" @selected(request('target') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="sr-only">Status paket</label>
                <select id="status" name="status" class="block w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
            <button type="submit" class="cursor-pointer rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300">Terapkan</button>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full min-w-[760px] text-left text-sm">
                <caption class="sr-only">Daftar paket bantuan standar</caption>
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3">Nama paket</th>
                        <th scope="col" class="px-5 py-3">Target</th>
                        <th scope="col" class="px-5 py-3">Komposisi</th>
                        <th scope="col" class="px-5 py-3">Status</th>
                        <th scope="col" class="px-5 py-3">Riwayat salur</th>
                        <th scope="col" class="px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($packages as $package)
                        <tr class="align-top hover:bg-slate-50/70">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.packages.show', $package) }}" class="font-semibold text-slate-900 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200">{{ $package->name }}</a>
                                @if ($package->description)<p class="mt-1 max-w-xs text-xs leading-5 text-slate-500">{{ $package->description }}</p>@endif
                            </td>
                            <td class="px-5 py-4 text-slate-700">{{ $package->target_label }}</td>
                            <td class="px-5 py-4 text-xs leading-6 text-slate-600">
                                @foreach ($package->packageItems as $packageItem)
                                    <div>{{ $packageItem->item?->name ?? 'Barang tidak tersedia' }} <span class="font-semibold">× {{ $packageItem->quantity }}</span></div>
                                @endforeach
                            </td>
                            <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $package->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $package->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="px-5 py-4 text-slate-700">{{ $package->distributions_count }} kali</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-x-3 gap-y-2">
                                    <a href="{{ route('admin.packages.show', $package) }}" class="font-semibold text-blue-700 hover:text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-200">Hitung</a>
                                    <a href="{{ route('admin.packages.edit', $package) }}" class="font-semibold text-slate-700 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200">Edit</a>
                                    @if ($package->is_active)
                                        <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" onsubmit="return confirm('{{ $package->distributions_count > 0 ? 'Paket sudah pernah disalurkan dan akan dinonaktifkan. Lanjutkan?' : 'Hapus paket ini?' }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="cursor-pointer font-semibold text-rose-700 hover:text-rose-900 focus:outline-none focus:ring-2 focus:ring-rose-200">{{ $package->distributions_count > 0 ? 'Nonaktifkan' : 'Hapus' }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center"><p class="font-semibold text-slate-700">Belum ada paket bantuan</p><p class="mt-1 text-sm text-slate-500">Tambahkan paket pertama dengan minimal satu barang dan jumlahnya.</p><a href="{{ route('admin.packages.create') }}" class="mt-4 inline-block font-semibold text-blue-700 hover:text-blue-900">Tambah paket</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $packages->links() }}</div>
    </div>
@endcomponent
