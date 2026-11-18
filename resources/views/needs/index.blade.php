@component('layouts.auth')
    <div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6">
        <div class="mx-auto max-w-6xl space-y-8">
            <header class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-blue-600">ResQHub</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">Papan Kebutuhan Bantuan</h1>
                    <p class="mt-1 text-sm text-slate-600">Lihat kebutuhan terbuka dari posko dan pilih bantuan yang paling mendesak.</p>
                </div>
                <a href="{{ route('login') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Masuk</a>
            </header>

            <form method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5">
                <select name="event_id" class="cursor-pointer rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua kejadian</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}" @selected((string) request('event_id') === (string) $event->id)>{{ $event->name }}</option>
                    @endforeach
                </select>
                <select name="category" class="cursor-pointer rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $value => $label)
                        <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input name="region" value="{{ request('region') }}" placeholder="Cari wilayah posko" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <select name="priority" class="cursor-pointer rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua prioritas</option>
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="cursor-pointer rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Terapkan</button>
            </form>

            <div class="grid gap-5 md:grid-cols-2">
                @forelse ($needs as $need)
                    @php
                        $progress = $need->qty_needed > 0
                            ? min(100, (int) round(($need->qty_fulfilled / $need->qty_needed) * 100))
                            : 0;
                    @endphp
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $need->shelter->name }}</p>
                                <h2 class="mt-1 text-lg font-bold text-slate-900">{{ $need->item->name }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ $need->shelter->address }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $need->priority === 'mendesak' ? 'bg-rose-50 text-rose-700' : ($need->priority === 'tinggi' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">{{ $priorities[$need->priority] }}</span>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-xs text-slate-500">Kebutuhan</p>
                                <p class="mt-1 font-semibold text-slate-800">{{ number_format($need->qty_needed) }} {{ $need->item->unit }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Batas waktu</p>
                                <p class="mt-1 font-semibold text-slate-800">{{ $need->deadline?->format('d/m/Y') ?? 'Tidak ditentukan' }}</p>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="mb-1 flex items-center justify-between text-xs text-slate-500">
                                <span>Terpenuhi {{ number_format($need->qty_fulfilled) }} dari {{ number_format($need->qty_needed) }}</span>
                                <span>{{ $progress }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100">
                                <div class="h-2 rounded-full bg-blue-600" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-sm text-slate-500 md:col-span-2">
                        Belum ada kebutuhan terbuka yang sesuai filter.
                    </div>
                @endforelse
            </div>

            <div>{{ $needs->links() }}</div>
        </div>
    </div>
@endcomponent
