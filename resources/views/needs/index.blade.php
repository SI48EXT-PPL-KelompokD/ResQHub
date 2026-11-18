@component('layouts.public', ['title' => 'Papan Kebutuhan Bantuan', 'description' => 'Daftar kebutuhan terbuka dari posko evakuasi, diurutkan dari yang paling mendesak.'])
    <div class="bg-paper px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-8">
            <header>
                <h1 class="text-3xl font-extrabold tracking-tight text-ink">Papan Kebutuhan Bantuan</h1>
                <p class="mt-2 text-[15px] text-slate-700">Kebutuhan terbuka dari posko, diurutkan dari yang paling mendesak.</p>
            </header>

            <form method="GET" class="grid min-w-0 gap-3 border border-line bg-white p-4 sm:grid-cols-2 lg:grid-cols-5">
                <select class="rq-input min-w-0" name="event_id">
                    <option value="">Semua kejadian</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}" @selected((string) request('event_id') === (string) $event->id)>{{ $event->name }}</option>
                    @endforeach
                </select>
                <select class="rq-input min-w-0" name="category">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $value => $label)
                        <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input name="region" value="{{ request('region') }}" placeholder="Cari wilayah posko" aria-label="Cari wilayah posko" class="rq-input min-w-0">
                <select class="rq-input min-w-0" name="priority">
                    <option value="">Semua prioritas</option>
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rq-btn">Terapkan</button>
            </form>

            <div class="grid gap-5 md:grid-cols-2">
                @forelse ($needs as $need)
                    @php
                        $progress = $need->qty_needed > 0
                            ? min(100, (int) round(($need->qty_fulfilled / $need->qty_needed) * 100))
                            : 0;
                    @endphp
                    <article class="border border-line bg-white p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-600">{{ $need->shelter->name }}</p>
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
                            <div class="h-1.5 bg-slate-200">
                                <div class="h-1.5 bg-brand-600" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        @php
                            $stillNeeded = max(0, $need->qty_needed - $need->qty_fulfilled - $need->qty_pledged);
                            $canDonate = $stillNeeded > 0 && (auth()->guest() || auth()->user()->isDonatur());
                        @endphp
                        <div class="mt-5 flex items-center justify-between gap-4 border-t border-line pt-4">
                            <p class="text-sm text-slate-700">
                                @if ($stillNeeded > 0)
                                    Masih dibutuhkan <strong class="font-bold text-ink tabular-nums">{{ number_format($stillNeeded, 0, ',', '.') }} {{ $need->item->unit }}</strong>
                                @else
                                    Sudah dijanjikan penuh
                                @endif
                            </p>
                            @if ($canDonate)
                                <a href="{{ route('donor.goods-donations.create', ['aid_need' => $need->id]) }}" aria-label="Donasikan {{ $need->item->name }} untuk {{ $need->shelter->name }}" class="rq-btn h-10 shrink-0 px-4 text-sm">Donasikan</a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="border border-dashed border-slate-300 bg-white p-12 text-center text-sm text-slate-500 md:col-span-2">
                        Belum ada kebutuhan terbuka yang sesuai filter.
                    </div>
                @endforelse
            </div>

            <div>{{ $needs->links() }}</div>
        </div>
    </div>
@endcomponent
