@php
    $badge = match ($report->status) {
        \App\Models\DisasterReport::STATUS_ACCEPTED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        \App\Models\DisasterReport::STATUS_REJECTED => 'bg-rose-50 text-rose-700 border-rose-200',
        default => 'bg-amber-50 text-amber-700 border-amber-200',
    };
@endphp
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="flex items-start gap-3 min-w-0">
            <a href="{{ route('admin.reports.index') }}" wire:navigate class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-white border border-transparent hover:border-slate-200 transition" title="Kembali ke daftar laporan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full border {{ $badge }}">{{ $report->status_label }}</span>
                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $report->disaster_type_label }}</span>
                </div>
                <h1 class="mt-2 text-xl sm:text-2xl font-bold tracking-tight text-slate-900 break-words">{{ $report->title }}</h1>
                <p class="text-sm text-slate-500 mt-1">Detail Laporan Kejadian</p>
            </div>
        </div>

        @can('update', $report)
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('admin.reports.edit', $report) }}" wire:navigate class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Ubah</a>
                <button
                    type="button"
                    wire:click="delete"
                    wire:confirm="Hapus laporan ini? Laporan dan fotonya akan dihapus permanen."
                    wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-rose-200 bg-white text-sm font-semibold text-rose-600 hover:bg-rose-50 disabled:opacity-60 transition"
                >Hapus</button>
            </div>
        @endcan
    </div>

    @if ($statusMessage)
        <div class="flex items-start space-x-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $statusMessage }}</span>
        </div>
    @endif

    @if ($report->status === \App\Models\DisasterReport::STATUS_REJECTED && $report->reject_reason)
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm text-rose-800">
            <span class="font-semibold">Alasan penolakan:</span> {{ $report->reject_reason }}
        </div>
    @elseif ($report->status === \App\Models\DisasterReport::STATUS_ACCEPTED)
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-800">
            Laporan diterima{{ $report->disasterEvent ? ' dan dikaitkan ke kejadian "'.$report->disasterEvent->name.'"' : '' }}. Laporan yang sudah diverifikasi tidak dapat diubah atau dihapus.
        </div>
    @elseif (! $report->isPending())
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-600">Laporan yang sudah diverifikasi tidak dapat diubah atau dihapus.</div>
    @endif

    <!-- Informasi Kejadian -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-4">
        <h2 class="text-sm font-bold text-slate-800">Informasi Kejadian</h2>

        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Waktu Kejadian</dt>
                <dd class="mt-0.5 text-slate-800">{{ $report->occurred_at?->translatedFormat('d M Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Perkiraan Terdampak</dt>
                <dd class="mt-0.5 text-slate-800">{{ $report->estimated_affected !== null ? number_format($report->estimated_affected, 0, ',', '.').' jiwa' : 'Tidak diisi' }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Dikirim</dt>
                <dd class="mt-0.5 text-slate-800">{{ $report->created_at?->translatedFormat('d M Y H:i') }}</dd>
            </div>
        </dl>

        <div>
            <h3 class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Deskripsi Kondisi</h3>
            <p class="mt-1 text-sm text-slate-700 whitespace-pre-line break-words">{{ $report->description }}</p>
        </div>
    </section>

    <!-- Lokasi Kejadian -->
    <section
        class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-4"
        x-data="{
            map: null,
            init() {
                this.setupMap();
            },
            setupMap() {
                const container = this.$refs.mapContainer;
                if (!container) return;
                if (typeof L === 'undefined') {
                    setTimeout(() => this.setupMap(), 200);
                    return;
                }
                if (container._leaflet_id) {
                    container._leaflet_id = null;
                }
                const point = [{{ $report->lat }}, {{ $report->lng }}];
                this.map = L.map(container, { center: point, zoom: 15, attributionControl: false, scrollWheelZoom: false });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(this.map);
                L.marker(point).addTo(this.map);
                setTimeout(() => this.map && this.map.invalidateSize(), 250);
            }
        }"
    >
        <div>
            <h2 class="text-sm font-bold text-slate-800">Lokasi Kejadian</h2>
            <p class="text-sm text-slate-700 mt-1">{{ $report->address }}</p>
            <p class="text-xs font-mono text-slate-500 mt-0.5">{{ $report->lat }}, {{ $report->lng }}</p>
        </div>

        <div wire:ignore class="rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
            <div x-ref="mapContainer" class="w-full" style="height: 260px; min-height: 260px;"></div>
        </div>
    </section>

    <!-- Foto Kejadian -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-4">
        <h2 class="text-sm font-bold text-slate-800">Foto Kejadian</h2>

        @if ($report->photos->isEmpty())
            <p class="text-sm text-slate-500">Tidak ada foto yang dilampirkan.</p>
        @else
            <div class="grid grid-cols-3 gap-3">
                @foreach ($report->photos as $photo)
                    <a wire:key="photo-{{ $photo->id }}" href="{{ $photo->url }}" target="_blank" rel="noopener" class="block aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                        <img src="{{ $photo->url }}" alt="Foto {{ $report->title }}" class="w-full h-full object-cover" loading="lazy">
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <!-- Riwayat Status -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-4">
        <h2 class="text-sm font-bold text-slate-800">Riwayat Status</h2>

        @if ($history->isEmpty())
            <p class="text-sm text-slate-500">Belum ada riwayat.</p>
        @else
            <ol class="relative border-l border-slate-200 ml-2 space-y-5">
                @foreach ($history as $item)
                    @php
                        $dot = match ($item['action']) {
                            'laporan_diterima' => 'bg-emerald-500',
                            'laporan_ditolak' => 'bg-rose-500',
                            'laporan_diubah' => 'bg-slate-400',
                            default => 'bg-amber-500',
                        };
                    @endphp
                    <li class="ml-5">
                        <span class="absolute -left-[5px] mt-1.5 w-2.5 h-2.5 rounded-full {{ $dot }}"></span>
                        <p class="text-sm font-semibold text-slate-800">{{ $item['label'] }}</p>
                        <p class="text-xs text-slate-500">{{ $item['at']?->translatedFormat('d M Y H:i') }} oleh {{ $item['actor'] }}</p>
                        @if ($item['note'])
                            <p class="mt-1 text-xs text-slate-600">{{ $item['note'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        @endif
    </section>
</div>
