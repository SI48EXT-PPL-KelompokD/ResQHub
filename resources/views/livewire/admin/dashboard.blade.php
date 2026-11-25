@php
    $user = Auth::user();
    $firstName = \Illuminate\Support\Str::of($user->name)->replaceMatches('/^(dr|drg|ns|bd)\.?\s+/i', '')->before(' ');
    $heading = match ($user->role) {
        'admin' => 'Ringkasan Pusdalops',
        'koordinator' => 'Ringkasan posko Anda',
        default => "Halo, {$firstName}",
    };
    $panel = 'border border-slate-200 bg-white';
    $panelHead = 'flex items-baseline justify-between gap-4 border-b border-slate-200 px-5 py-3.5';
    $panelTitle = 'text-base font-bold text-ink';
    $priorityTone = ['mendesak' => 'text-red-800', 'tinggi' => 'text-amber-800', 'normal' => 'text-slate-600'];
@endphp

<div class="mx-auto max-w-7xl space-y-6">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-ink">{{ $heading }}</h1>
            @if ($activeEvent)
                <p class="mt-1 text-[15px] text-slate-700">Kejadian aktif: {{ $activeEvent->name }}</p>
            @endif
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($user->isWarga())
                <span aria-disabled="true" title="Fitur ini sedang disiapkan" class="inline-flex h-11 cursor-not-allowed items-center rounded-md border border-slate-200 bg-slate-100 px-5 text-[15px] font-semibold text-slate-400">Kirim SOS (segera)</span>
                <a href="{{ route('admin.reports.create') }}" wire:navigate class="rq-btn">Buat Laporan</a>
            @elseif ($user->isDonatur())
                <a href="{{ route('donor.goods-donations.create') }}" class="rq-btn">Janjikan Donasi</a>
            @elseif ($user->isRelawan())
                <a href="{{ route('admin.tasks') }}" wire:navigate class="rq-btn">Lihat Tugas Saya</a>
            @endif
        </div>
    </header>

    {{-- Angka utama --}}
    <dl class="grid grid-cols-2 border border-slate-200 bg-white lg:grid-cols-4">
        @foreach ($metrics as $metric)
            <div class="relative border-slate-200 p-5 {{ $loop->odd ? '' : 'border-l' }} {{ $loop->iteration > 2 ? 'border-t lg:border-t-0' : '' }} {{ $loop->iteration === 3 ? 'lg:border-l' : '' }} {{ $metric['href'] ? 'hover:bg-slate-50' : '' }}">
                <dt class="text-sm font-semibold text-slate-700">
                    @if ($metric['href'])
                        <a href="{{ $metric['href'] }}" class="after:absolute after:inset-0">{{ $metric['label'] }}</a>
                    @else
                        {{ $metric['label'] }}
                    @endif
                </dt>
                <dd class="mt-1 text-3xl font-extrabold tracking-tight text-ink tabular-nums">{{ $metric['value'] }}</dd>
                <dd class="mt-0.5 text-[13px] text-slate-600">{{ $metric['hint'] }}</dd>
            </div>
        @endforeach
    </dl>

    @if (! empty($triageSummary) && ($user->isAdmin() || $user->isKoordinator() || $user->isNakes()))
        <section class="{{ $panel }} flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="{{ $panelTitle }}">Triase hari ini</h2>
                <p class="mt-0.5 text-sm text-slate-700">{{ $triageSummary['totals']['total'] }} pemeriksaan, {{ $triageSummary['unreferred_merah'] ?? 0 }} triase merah belum dirujuk.</p>
            </div>
            <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-sm font-semibold">
                <span class="text-green-800">Hijau {{ $triageSummary['totals']['hijau'] }}</span>
                <span class="text-amber-800">Kuning {{ $triageSummary['totals']['kuning'] }}</span>
                <span class="text-red-800">Merah {{ $triageSummary['totals']['merah'] }}</span>
                <a href="{{ route('admin.health-checks') }}" wire:navigate class="rq-link">Kelola pemeriksaan</a>
            </div>
        </section>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
        <div class="min-w-0 space-y-6">
            {{-- Antrean verifikasi: admin --}}
            @isset($pendingReports)
                <section class="{{ $panel }}">
                    <div class="{{ $panelHead }}">
                        <h2 class="{{ $panelTitle }}">Laporan menunggu verifikasi</h2>
                    </div>
                    <ul class="divide-y divide-slate-200">
                        @forelse ($pendingReports as $report)
                            <li wire:key="pending-{{ $report->id }}" class="px-5 py-3">
                                <p class="font-semibold text-ink">{{ $report->title }}</p>
                                <p class="mt-0.5 truncate text-[13px] text-slate-600">{{ $report->disaster_type_label }} · {{ $report->address }} · {{ $report->created_at?->locale('id')->diffForHumans() }}</p>
                            </li>
                        @empty
                            <li class="px-5 py-8 text-center text-sm text-slate-600">Semua laporan sudah diverifikasi.</li>
                        @endforelse
                    </ul>
                </section>
            @endisset

            {{-- SOS: hanya admin dan koordinator --}}
            @isset($recentSos)
                <section class="{{ $panel }}">
                    <div class="{{ $panelHead }}">
                        <h2 class="{{ $panelTitle }}">Permintaan SOS terbaru</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[520px] text-left text-sm">
                            <thead class="border-b border-slate-200 text-[13px] text-slate-600">
                                <tr>
                                    <th scope="col" class="px-5 py-2.5 font-semibold">Pemohon</th>
                                    <th scope="col" class="px-3 py-2.5 font-semibold">Jenis</th>
                                    <th scope="col" class="px-3 py-2.5 text-right font-semibold">Orang</th>
                                    <th scope="col" class="px-3 py-2.5 font-semibold">Status</th>
                                    <th scope="col" class="px-5 py-2.5"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($recentSos as $sos)
                                    <tr wire:key="sos-{{ $sos->id }}">
                                        <td class="px-5 py-3">
                                            <span class="block font-semibold text-ink">{{ $sos->requester_name }}</span>
                                            <span class="block max-w-[220px] truncate text-[13px] text-slate-600">{{ $sos->note }}</span>
                                        </td>
                                        <td class="px-3 py-3 capitalize">{{ $sos->help_type }}</td>
                                        <td class="px-3 py-3 text-right tabular-nums">{{ $sos->people_count }}</td>
                                        <td class="px-3 py-3 font-semibold capitalize {{ $sos->status === 'baru' ? 'text-red-800' : ($sos->status === 'ditangani' ? 'text-brand-700' : 'text-green-800') }}">{{ $sos->status }}</td>
                                        <td class="px-5 py-3 text-right">
                                            @if ($sos->status === 'baru')
                                                <a href="{{ route('admin.tasks', ['sos' => $sos->id]) }}" wire:navigate class="rq-link text-[13px] whitespace-nowrap">Buat tugas</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-600">Belum ada permintaan SOS.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endisset

            {{-- Tugas: relawan dan nakes --}}
            @isset($myTasks)
                <section class="{{ $panel }}">
                    <div class="{{ $panelHead }}">
                        <h2 class="{{ $panelTitle }}">Tugas saya</h2>
                        @if ($user->canAccessModule('modul_g'))
                            <a href="{{ route('admin.tasks') }}" wire:navigate class="rq-link text-sm">Semua tugas</a>
                        @endif
                    </div>
                    <ul class="divide-y divide-slate-200">
                        @forelse ($myTasks as $task)
                            <li class="flex items-start justify-between gap-4 px-5 py-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-ink">{{ $task->title }}</p>
                                    <p class="mt-0.5 text-[13px] text-slate-600">Mulai {{ \Carbon\Carbon::parse($task->start_at)->locale('id')->translatedFormat('d M Y, H.i') }}</p>
                                </div>
                                <span class="shrink-0 text-[13px] font-semibold {{ $task->assignment_status === 'ditawarkan' ? 'text-amber-800' : 'text-brand-700' }}">{{ $task->assignment_status === 'ditawarkan' ? 'Perlu dijawab' : 'Diterima' }}</span>
                            </li>
                        @empty
                            <li class="px-5 py-8 text-center text-sm text-slate-600">Belum ada tugas aktif.</li>
                        @endforelse
                    </ul>
                </section>
            @endisset

            {{-- Laporan milik warga --}}
            @isset($myReports)
                <section class="{{ $panel }}">
                    <div class="{{ $panelHead }}">
                        <h2 class="{{ $panelTitle }}">Laporan saya</h2>
                        <a href="{{ route('admin.reports.index') }}" wire:navigate class="rq-link text-sm">Semua laporan</a>
                    </div>
                    <ul class="divide-y divide-slate-200">
                        @forelse ($myReports as $report)
                            <li wire:key="mine-{{ $report->id }}">
                                <a href="{{ route('admin.reports.show', $report) }}" wire:navigate class="flex items-start justify-between gap-4 px-5 py-3 hover:bg-slate-50">
                                    <span class="min-w-0">
                                        <span class="block font-semibold text-ink">{{ $report->title }}</span>
                                        <span class="mt-0.5 block truncate text-[13px] text-slate-600">{{ $report->disaster_type_label }} · {{ $report->address }}</span>
                                    </span>
                                    <span class="shrink-0 text-[13px] font-semibold {{ $report->status === 'diterima' ? 'text-green-800' : ($report->status === 'ditolak' ? 'text-red-800' : 'text-amber-800') }}">{{ $report->status_label }}</span>
                                </a>
                            </li>
                        @empty
                            <li class="px-5 py-8 text-center text-sm text-slate-600">Anda belum pernah mengirim laporan.</li>
                        @endforelse
                    </ul>
                </section>
            @endisset

            {{-- Riwayat donasi --}}
            @isset($myGoodsDonations)
                <section class="{{ $panel }}">
                    <div class="{{ $panelHead }}">
                        <h2 class="{{ $panelTitle }}">Riwayat donasi</h2>
                        <a href="{{ route('donor.goods-donations.index') }}" class="rq-link text-sm">Semua donasi</a>
                    </div>
                    <ul class="divide-y divide-slate-200">
                        @forelse ($myGoodsDonations as $donation)
                            <li wire:key="donation-{{ $donation->id }}">
                                <a href="{{ route('donor.goods-donations.show', $donation) }}" class="flex items-start justify-between gap-4 px-5 py-3 hover:bg-slate-50">
                                    <span class="min-w-0">
                                        <span class="block font-semibold text-ink">{{ $donation->item_name }}, {{ $donation->quantity }} {{ $donation->item_unit }}</span>
                                        <span class="mt-0.5 block truncate text-[13px] text-slate-600">{{ $donation->code }} · {{ $donation->shelter_name }}</span>
                                    </span>
                                    <span class="shrink-0 text-[13px] font-semibold text-slate-700">{{ $donation->status->label() }}</span>
                                </a>
                            </li>
                        @empty
                            <li class="px-5 py-8 text-center text-sm text-slate-600">Anda belum memiliki janji donasi.</li>
                        @endforelse
                    </ul>
                </section>
            @endisset

            {{-- Peringatan fasilitas tidak berfungsi: koordinator (PBI-05) --}}
            @isset($brokenFacilities)
                @if ($brokenFacilities->isNotEmpty())
                    <section class="{{ $panel }} border-red-300" role="alert">
                        <div class="{{ $panelHead }}">
                            <h2 class="{{ $panelTitle }}">Peringatan fasilitas posko</h2>
                            <a href="{{ route('admin.shelters') }}" wire:navigate class="rq-link text-sm">Kelola fasilitas</a>
                        </div>
                        <ul class="divide-y divide-slate-200">
                            @foreach ($brokenFacilities as $facility)
                                <li wire:key="broken-facility-{{ $facility->id }}" class="flex items-center justify-between gap-4 px-5 py-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-ink">{{ $facility->type_label }}</p>
                                        <p class="mt-0.5 truncate text-[13px] text-slate-600">{{ $facility->shelter->name }}{{ $facility->note ? ' · '.$facility->note : '' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-[12px] font-semibold text-red-700">Tidak berfungsi</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            @endisset

            {{-- Stok menipis: admin dan koordinator --}}
            @isset($lowStocks)
                <section class="{{ $panel }}">
                    <div class="{{ $panelHead }}">
                        <h2 class="{{ $panelTitle }}">Stok menipis</h2>
                        <a href="{{ route('admin.stocks.index') }}" wire:navigate class="rq-link text-sm">Kelola stok</a>
                    </div>
                    <ul class="divide-y divide-slate-200">
                        @forelse ($lowStocks as $stock)
                            <li wire:key="stock-{{ $stock->id }}" class="flex items-center justify-between gap-4 px-5 py-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-ink">{{ $stock->item_name }}</p>
                                    <p class="mt-0.5 truncate text-[13px] text-slate-600">{{ $stock->shelter_name }} · sisa {{ $stock->quantity }} dari ambang {{ $stock->min_threshold }} {{ $stock->unit }}</p>
                                </div>
                                @if ($user->isKoordinator())
                                    <a href="{{ route('admin.aid-needs.create', ['item_id' => $stock->item_id, 'shelter_id' => $stock->shelter_id]) }}" wire:navigate class="rq-link shrink-0 text-[13px]">Ajukan kebutuhan</a>
                                @endif
                            </li>
                        @empty
                            <li class="px-5 py-8 text-center text-sm text-slate-600">Semua stok masih di atas ambang batas.</li>
                        @endforelse
                    </ul>
                </section>
            @endisset
        </div>

        <div class="min-w-0 space-y-6">
            {{-- Kebutuhan mendesak --}}
            @isset($urgentNeeds)
                <section id="kebutuhan-mendesak" class="{{ $panel }}">
                    <div class="{{ $panelHead }}">
                        <h2 class="{{ $panelTitle }}">Kebutuhan mendesak</h2>
                        <a href="{{ $user->isKoordinator() ? route('admin.needs.index') : route('needs.public') }}" class="rq-link text-sm">Semua</a>
                    </div>
                    <ul class="divide-y divide-slate-200">
                        @forelse ($urgentNeeds as $need)
                            @php
                                $pledged = min($need->qty_needed, $need->qty_fulfilled + $need->qty_pledged);
                                $percent = $need->qty_needed > 0 ? min(100, (int) round(($pledged / $need->qty_needed) * 100)) : 0;
                                $remaining = max(0, $need->qty_needed - $pledged);
                            @endphp
                            <li wire:key="need-{{ $need->id }}" class="px-5 py-3.5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-ink">{{ $need->item_name }}</p>
                                        <p class="mt-0.5 truncate text-[13px] text-slate-600">{{ $need->shelter_name }}</p>
                                    </div>
                                    <span class="shrink-0 text-[13px] font-bold capitalize {{ $priorityTone[$need->priority] ?? 'text-slate-600' }}">{{ $need->priority }}</span>
                                </div>
                                <div class="mt-2 h-1.5 bg-slate-200" role="presentation"><div class="h-1.5 bg-brand-600" style="width: {{ $percent }}%"></div></div>
                                <div class="mt-1.5 flex items-center justify-between gap-3 text-[13px] text-slate-700">
                                    <span class="tabular-nums">{{ number_format($pledged, 0, ',', '.') }} dari {{ number_format($need->qty_needed, 0, ',', '.') }} {{ $need->unit }}</span>
                                    @if ($user->isDonatur() && $remaining > 0)
                                        <a href="{{ route('donor.goods-donations.create', ['aid_need' => $need->id]) }}" class="rq-link">Donasikan</a>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="px-5 py-8 text-center text-sm text-slate-600">Tidak ada kebutuhan terbuka.</li>
                        @endforelse
                    </ul>
                </section>
            @endisset

            {{-- Peta posko dari data asli --}}
            @isset($mapPoints)
                <section class="{{ $panel }}">
                    <div class="{{ $panelHead }}">
                        <h2 class="{{ $panelTitle }}">Peta posko</h2>
                        <a href="{{ route('public.shelters') }}" class="rq-link text-sm">Perbesar</a>
                    </div>
                    <x-shelter-map :points="$mapPoints" class="h-64" />
                </section>
            @endisset
        </div>
    </div>
</div>
