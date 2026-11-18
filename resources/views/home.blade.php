@component('layouts.public', [
    'title' => 'Lapor Bencana dan Cari Posko Terdekat',
    'description' => 'Laporkan kejadian bencana, temukan posko evakuasi yang masih menerima pengungsi, dan lihat bantuan yang paling dibutuhkan saat ini.',
])
    @php
        $user = auth()->user();
        $canReport = ! $user || $user->isWarga();
        $statusTone = [
            'tersedia' => ['Tersedia', 'text-green-800', 'bg-green-700'],
            'hampir_penuh' => ['Hampir penuh', 'text-amber-800', 'bg-amber-600'],
            'penuh' => ['Penuh', 'text-red-800', 'bg-red-700'],
        ];
        $priorityTone = [
            'mendesak' => 'text-red-800',
            'tinggi' => 'text-amber-800',
            'normal' => 'text-slate-600',
        ];
    @endphp

    {{-- Pembuka: aksi utama berdampingan dengan peta posko yang sebenarnya --}}
    <section class="relative border-b border-line">
        <div class="mx-auto flex max-w-7xl flex-col justify-center px-4 py-12 sm:px-6 lg:min-h-[540px] lg:px-8 lg:py-16">
            <div class="lg:w-[calc(41.667%-2rem)] lg:pr-8">
            @if ($activeEvent)
                <p class="flex items-start gap-2 text-sm font-semibold text-red-800">
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-red-700" aria-hidden="true"></span>
                    <span>Kejadian aktif: {{ $activeEvent->name }}</span>
                </p>
            @endif

            <h1 class="mt-4 text-4xl leading-[1.08] font-extrabold tracking-tight text-ink sm:text-5xl">
                Saat bencana terjadi, mulai dari sini.
            </h1>
            <p class="mt-5 max-w-md text-lg leading-relaxed text-slate-700">
                Laporkan kejadian di sekitar Anda, temukan posko yang masih menerima pengungsi, dan lihat bantuan yang paling dibutuhkan.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                @if ($canReport)
                    <a href="{{ route('admin.reports.create') }}" class="rq-btn h-12 px-6 text-base">Laporkan Kejadian</a>
                @else
                    <a href="{{ route('admin.dashboard') }}" class="rq-btn h-12 px-6 text-base">Buka Dashboard</a>
                @endif
                <a href="{{ route('public.shelters') }}" class="rq-btn-outline h-12 px-6 text-base">Cari Posko Terdekat</a>
            </div>
            <p class="mt-4 text-sm text-slate-600">Mencari posko tidak perlu akun.</p>
            </div>
        </div>

        <div class="relative h-[300px] border-t border-line sm:h-[360px] lg:absolute lg:inset-y-0 lg:right-0 lg:left-[max(41.667%,calc(50%-6.667rem))] lg:h-auto lg:border-t-0 lg:border-l">
            <x-shelter-map :points="$mapPoints" class="absolute! inset-0 z-0" />
            <ul class="absolute bottom-3 left-3 z-[500] flex flex-wrap gap-x-4 gap-y-1 rounded-md bg-white px-3 py-2 text-[13px] font-medium shadow-sm">
                @foreach ($statusTone as [$label, $text, $dot])
                    <li class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full {{ $dot }}" aria-hidden="true"></span>{{ $label }}</li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Kondisi saat ini --}}
    <section aria-label="Kondisi saat ini" class="border-b border-line bg-paper">
        <dl class="mx-auto grid max-w-7xl grid-cols-2 px-4 sm:px-6 lg:grid-cols-4 lg:px-8">
            @foreach ([
                ['Kejadian aktif', $stats['events'], 'sedang ditangani'],
                ['Posko beroperasi', $stats['shelters'], 'siap dituju'],
                ['Tempat tersisa', number_format($stats['remaining'], 0, ',', '.'), 'di seluruh posko'],
                ['Kebutuhan terbuka', $stats['needs'], 'menunggu bantuan'],
            ] as $index => [$label, $value, $hint])
                <div class="py-6 {{ $index % 2 === 1 ? 'border-l border-line pl-5 sm:pl-8' : '' }} {{ $index > 1 ? 'border-t border-line lg:border-t-0' : '' }} {{ $index === 2 ? 'lg:border-l lg:pl-8' : '' }}">
                    <dt class="text-sm font-semibold text-slate-700">{{ $label }}</dt>
                    <dd class="mt-1 text-4xl font-extrabold tracking-tight text-ink tabular-nums">{{ $value }}</dd>
                    <dd class="mt-0.5 text-sm text-slate-600">{{ $hint }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- Posko dan kebutuhan --}}
    <section class="mx-auto grid max-w-7xl gap-14 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:gap-20 lg:px-8">
        <div>
            <div class="flex items-baseline justify-between gap-4">
                <h2 class="text-2xl font-extrabold tracking-tight">Posko yang masih bisa dituju</h2>
                <a href="{{ route('public.shelters') }}" class="rq-link shrink-0 text-sm">Lihat peta</a>
            </div>

            <ul class="mt-6 divide-y divide-line border-y border-line">
                @forelse ($shelters as $shelter)
                    @php [$label, $text, $bar] = $statusTone[$shelter->capacity_status] ?? $statusTone['tersedia']; @endphp
                    <li class="py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="font-bold">{{ $shelter->name }}</h3>
                                <p class="mt-0.5 text-sm text-slate-600">{{ $shelter->address }}</p>
                            </div>
                            <p class="shrink-0 text-right">
                                <span class="block text-2xl leading-none font-extrabold tabular-nums">{{ number_format($shelter->remaining_capacity, 0, ',', '.') }}</span>
                                <span class="mt-1 block text-[13px] text-slate-600">tempat tersisa</span>
                            </p>
                        </div>
                        <div class="mt-3 flex items-center gap-3">
                            <div class="h-1.5 flex-1 bg-slate-200" role="presentation">
                                <div class="h-1.5 {{ $bar }}" style="width: {{ min(100, $shelter->occupancy_percentage) }}%"></div>
                            </div>
                            <span class="w-24 text-right text-[13px] font-semibold {{ $text }}">{{ $label }}</span>
                            <a href="{{ $shelter->google_maps_url }}" target="_blank" rel="noopener" class="rq-link text-[13px]">Arahkan</a>
                        </div>
                    </li>
                @empty
                    <li class="py-8 text-[15px] text-slate-600">Belum ada posko yang beroperasi saat ini.</li>
                @endforelse
            </ul>
        </div>

        <div>
            <div class="flex items-baseline justify-between gap-4">
                <h2 class="text-2xl font-extrabold tracking-tight">Paling dibutuhkan sekarang</h2>
                <a href="{{ route('needs.public') }}" class="rq-link shrink-0 text-sm">Semua kebutuhan</a>
            </div>

            <ul class="mt-6 divide-y divide-line border-y border-line">
                @forelse ($urgentNeeds as $need)
                    @php
                        $received = min($need->qty_needed, $need->qty_fulfilled + $need->qty_pledged);
                        $progress = $need->qty_needed > 0 ? min(100, (int) round(($received / $need->qty_needed) * 100)) : 0;
                    @endphp
                    <li class="py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="font-bold">{{ $need->item->name }}</h3>
                                <p class="mt-0.5 text-sm text-slate-600">{{ $need->shelter->name }}</p>
                            </div>
                            <span class="shrink-0 text-[13px] font-bold capitalize {{ $priorityTone[$need->priority] ?? 'text-slate-600' }}">{{ $need->priority }}</span>
                        </div>
                        <div class="mt-3 flex items-center gap-3">
                            <div class="h-1.5 flex-1 bg-slate-200" role="presentation">
                                <div class="h-1.5 bg-brand-600" style="width: {{ $progress }}%"></div>
                            </div>
                            <span class="text-[13px] text-slate-700 tabular-nums">{{ number_format($received, 0, ',', '.') }} dari {{ number_format($need->qty_needed, 0, ',', '.') }} {{ $need->item->unit }}</span>
                        </div>
                    </li>
                @empty
                    <li class="py-8 text-[15px] text-slate-600">Belum ada kebutuhan terbuka saat ini.</li>
                @endforelse
            </ul>
        </div>
    </section>

    {{-- Alur penanganan --}}
    <section class="bg-ink text-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="max-w-xl text-2xl font-extrabold tracking-tight sm:text-3xl">Dari laporan Anda sampai bantuan tiba</h2>
            <ol class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Warga melapor', 'Jenis kejadian, lokasi di peta, dan foto dikirim dari tempat kejadian.'],
                    ['Pusdalops memverifikasi', 'Laporan diperiksa, lalu ditetapkan sebagai kejadian resmi.'],
                    ['Posko digerakkan', 'Koordinator mencatat pengungsi dan mengajukan kebutuhan posko.'],
                    ['Bantuan disalurkan', 'Donatur dan relawan mengisi kebutuhan yang paling mendesak.'],
                ] as $index => [$title, $text])
                    <li class="border-t border-white/30 pt-4">
                        <span class="text-sm font-bold text-white/70 tabular-nums">Langkah {{ $index + 1 }}</span>
                        <h3 class="mt-2 text-lg font-bold">{{ $title }}</h3>
                        <p class="mt-2 text-[15px] leading-relaxed text-white/75">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Ajakan bergabung --}}
    @guest
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Ambil peran Anda</h2>
            <ul class="mt-8 divide-y divide-line border-y border-line">
                @foreach ([
                    ['warga', 'Warga', 'Kirim laporan kejadian dan pantau statusnya sampai diverifikasi.', 'Daftar sebagai warga'],
                    ['relawan', 'Relawan', 'Terima tawaran tugas yang sesuai dengan keahlian dan jangkauan Anda.', 'Daftar sebagai relawan'],
                    ['donatur', 'Donatur', 'Janjikan barang yang benar-benar dibutuhkan posko dan lacak statusnya.', 'Daftar sebagai donatur'],
                ] as [$role, $title, $text, $cta])
                    <li class="grid gap-2 py-6 md:grid-cols-[12rem_1fr_auto] md:items-center md:gap-8">
                        <h3 class="text-xl font-extrabold">{{ $title }}</h3>
                        <p class="text-[15px] text-slate-700">{{ $text }}</p>
                        <a href="{{ route('register', ['peran' => $role]) }}" class="rq-link text-[15px]">{{ $cta }}</a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endguest
@endcomponent
