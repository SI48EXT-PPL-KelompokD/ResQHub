@component('layouts.admin')
    <div class="mx-auto max-w-7xl space-y-6">
        @if (session('success'))
            <div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="space-y-1">
                <p class="text-sm font-medium text-blue-600">Modul D · Kebutuhan</p>
                <h1 class="text-2xl font-bold text-slate-900">Kebutuhan Bantuan Posko</h1>
                <p class="max-w-2xl text-sm text-slate-600">Catat kebutuhan nyata posko agar donatur dapat membantu berdasarkan prioritas dan progres pemenuhan.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('needs.public') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Lihat papan publik</a>
                <a href="{{ route('admin.needs.create') }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Tambah kebutuhan</a>
            </div>
        </div>

        <form method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-3">
            <select name="status" class="cursor-pointer rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Semua status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="priority" class="cursor-pointer rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Semua prioritas</option>
                @foreach ($priorities as $value => $label)
                    <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="cursor-pointer rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Terapkan filter</button>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full min-w-[960px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Posko</th>
                        <th class="px-5 py-3">Barang</th>
                        <th class="px-5 py-3">Jumlah</th>
                        <th class="px-5 py-3">Prioritas</th>
                        <th class="px-5 py-3">Batas waktu</th>
                        <th class="px-5 py-3">Progres</th>
                        <th class="px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($needs as $need)
                        @php
                            $progress = $need->qty_needed > 0
                                ? min(100, (int) round(($need->qty_fulfilled / $need->qty_needed) * 100))
                                : 0;
                        @endphp
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-800">{{ $need->shelter->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $need->shelter->address }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-800">{{ $need->item->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $need->item->unit }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-700">
                                <p>{{ number_format($need->qty_needed) }} {{ $need->item->unit }}</p>
                                <p class="mt-1 text-xs text-slate-500">Dijanjikan {{ number_format($need->qty_pledged) }} · Terpenuhi {{ number_format($need->qty_fulfilled) }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $need->priority === 'mendesak' ? 'bg-rose-50 text-rose-700' : ($need->priority === 'tinggi' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $priorities[$need->priority] }}
                                </span>
                                <p class="mt-2 text-xs text-slate-500">{{ ucfirst($need->status) }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-700">{{ $need->deadline?->format('d/m/Y') ?? 'Tidak ditentukan' }}</td>
                            <td class="px-5 py-4">
                                <div class="min-w-32">
                                    <div class="mb-1 flex items-center justify-between text-xs text-slate-500">
                                        <span>{{ $progress }}%</span>
                                        <span>{{ number_format($need->remainingQuantity()) }} tersisa</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100">
                                        <div class="h-2 rounded-full bg-blue-600" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if ($need->status === 'terbuka')
                                    <a href="{{ route('admin.needs.edit', $need) }}" class="font-semibold text-blue-600 hover:text-blue-800">Edit</a>
                                    <form method="POST" action="{{ route('admin.needs.destroy', $need) }}" class="mt-3 space-y-2">
                                        @csrf
                                        @method('DELETE')
                                        <input name="close_reason" required maxlength="255" placeholder="Alasan hapus/tutup" class="w-40 rounded-lg border border-slate-300 px-2.5 py-2 text-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        <button type="submit" class="block cursor-pointer font-semibold text-rose-600 hover:text-rose-800">Hapus/tutup</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">Tidak ada aksi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500">Belum ada kebutuhan pada posko Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $needs->links() }}</div>
    </div>
@endcomponent
