@php
    $statusBadge = fn (string $status) => match ($status) {
        \App\Models\DisasterReport::STATUS_ACCEPTED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        \App\Models\DisasterReport::STATUS_REJECTED => 'bg-rose-50 text-rose-700 border-rose-200',
        default => 'bg-amber-50 text-amber-700 border-amber-200',
    };
@endphp
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex items-start gap-3 min-w-0">
        <a href="{{ route('admin.reports.verification') }}" wire:navigate class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-white border border-transparent hover:border-slate-200 transition" title="Kembali ke antrean verifikasi">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full border {{ $statusBadge($report->status) }}">{{ $report->status_label }}</span>
                <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $report->disaster_type_label }}</span>
            </div>
            <h1 class="mt-2 text-xl sm:text-2xl font-bold tracking-tight text-slate-900 break-words">{{ $report->title }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                Dilaporkan oleh {{ $report->reporter?->name ?? '-' }}@if ($report->reporter?->email) ({{ $report->reporter->email }})@endif
            </p>
        </div>
    </div>

    @if ($statusMessage)
        <div class="flex items-start space-x-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $statusMessage }}</span>
        </div>
    @endif

    @if ($report->status === \App\Models\DisasterReport::STATUS_REJECTED)
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm text-rose-800">
            <span class="font-semibold">Ditolak{{ $report->verifier ? ' oleh '.$report->verifier->name : '' }}.</span>
            Alasan penolakan: {{ $report->reject_reason ?? '-' }}
        </div>
    @elseif ($report->status === \App\Models\DisasterReport::STATUS_ACCEPTED)
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-800">
            <span class="font-semibold">Diterima{{ $report->verifier ? ' oleh '.$report->verifier->name : '' }}</span>{{ $report->disasterEvent ? ' dan dikaitkan ke kejadian "'.$report->disasterEvent->name.'"' : '' }}.
        </div>
    @endif

    <!-- Indikasi Duplikat -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-4">
        <div>
            <h2 class="text-sm font-bold text-slate-800">Laporan Lain dalam Radius {{ $radiusKm }} km</h2>
            <p class="text-xs text-slate-500 mt-0.5">Periksa kemungkinan laporan ganda sebelum membuat kejadian baru.</p>
        </div>

        @if ($nearby->isEmpty())
            <p class="text-sm text-slate-500">Tidak ada laporan lain di sekitar titik ini.</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($nearby as $other)
                    <li wire:key="nearby-{{ $other->id }}" class="py-3 flex items-center gap-3">
                        <span class="shrink-0 w-16 text-xs font-mono font-semibold text-slate-700">{{ number_format($other->distance_km, 2, ',', '.') }} km</span>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('admin.reports.verify', $other) }}" wire:navigate class="block text-sm font-semibold text-slate-800 hover:text-blue-700 truncate">{{ $other->title }}</a>
                            <p class="text-[11px] text-slate-500 truncate">{{ $other->disaster_type_label }} · {{ $other->occurred_at?->translatedFormat('d M Y H:i') }}</p>
                        </div>
                        <span class="shrink-0 text-[11px] font-semibold px-2 py-0.5 rounded-full border {{ $statusBadge($other->status) }}">{{ $other->status_label }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    @can('verify', $report)
        <!-- Keputusan Verifikasi -->
        <section class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-5">
            <h2 class="text-sm font-bold text-slate-800">Keputusan Verifikasi</h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <!-- Terima -->
                <form wire:submit="accept" class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-4 space-y-3">
                    <h3 class="text-sm font-semibold text-emerald-800">Terima Laporan</h3>

                    <div class="flex flex-wrap gap-4 text-sm text-slate-700">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" wire:model.live="eventMode" value="existing" class="text-blue-600">
                            <span>Kaitkan ke kejadian yang ada</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" wire:model.live="eventMode" value="new" class="text-blue-600">
                            <span>Buat kejadian baru</span>
                        </label>
                    </div>

                    @if ($eventMode === 'new')
                        <div>
                            <label for="new-event-name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Kejadian</label>
                            <input id="new-event-name" type="text" wire:model="new_event_name" maxlength="150" placeholder="Contoh: Banjir Baleendah November 2026" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                            @error('new_event_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="new-event-severity" class="block text-xs font-semibold text-slate-700 mb-1">Tingkat Keparahan</label>
                                <select id="new-event-severity" wire:model="new_event_severity" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                                    <option value="">Pilih tingkat</option>
                                    @foreach ($severities as $severity)
                                        <option value="{{ $severity }}">{{ ucfirst($severity) }}</option>
                                    @endforeach
                                </select>
                                @error('new_event_severity') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="new-event-regency" class="block text-xs font-semibold text-slate-700 mb-1">Kabupaten/Kota</label>
                                <input id="new-event-regency" type="text" wire:model="new_event_regency" maxlength="100" placeholder="Contoh: Kabupaten Bandung" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                                @error('new_event_regency') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-500">Jenis bencana, titik lokasi, dan waktu mulai kejadian baru diambil dari laporan ini.</p>
                    @else
                        <div>
                            <label for="disaster-event" class="block text-xs font-semibold text-slate-700 mb-1">Kejadian Bencana Aktif</label>
                            <select id="disaster-event" wire:model="disaster_event_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                                <option value="">Pilih kejadian</option>
                                @foreach ($events as $event)
                                    <option value="{{ $event->id }}">{{ $event->name }} ({{ \App\Models\DisasterReport::disasterTypeLabel($event->disaster_type) }}, {{ $event->regency }})</option>
                                @endforeach
                            </select>
                            @if ($events->isEmpty())
                                <p class="mt-1 text-[11px] text-slate-500">Belum ada kejadian aktif. Pilih "Buat kejadian baru".</p>
                            @endif
                            @error('disaster_event_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <button type="submit" wire:loading.attr="disabled" wire:target="accept" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-semibold transition">Terima Laporan</button>
                </form>

                <!-- Tolak -->
                <form wire:submit="reject" wire:confirm="Tolak laporan ini? Keputusan tidak dapat diubah." class="rounded-xl border border-rose-200 bg-rose-50/40 p-4 space-y-3">
                    <h3 class="text-sm font-semibold text-rose-800">Tolak Laporan</h3>
                    <div>
                        <label for="reject-reason" class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penolakan</label>
                        <textarea id="reject-reason" wire:model="reject_reason" rows="4" maxlength="1000" placeholder="Jelaskan alasan penolakan. Alasan ini dikirim ke pelapor." class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-600"></textarea>
                        @error('reject_reason') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" wire:loading.attr="disabled" wire:target="reject" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-rose-300 bg-white hover:bg-rose-50 disabled:opacity-60 text-rose-700 text-sm font-semibold transition">Tolak Laporan</button>
                </form>
            </div>
        </section>
    @endcan

    @include('livewire.report.partials.detail-sections')
</div>
