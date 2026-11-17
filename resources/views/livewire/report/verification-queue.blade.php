<div class="max-w-6xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Verifikasi Laporan Kejadian</h1>
            <p class="text-sm text-slate-500 mt-1">Antrean laporan warga untuk diperiksa Admin Pusdalops. Laporan yang menunggu verifikasi tampil paling atas.</p>
        </div>
        <span class="inline-flex items-center self-start sm:self-auto px-3 py-1.5 rounded-full border border-amber-200 bg-amber-50 text-amber-700 text-xs font-semibold">
            {{ $pendingCount }} menunggu verifikasi
        </span>
    </div>

    <!-- Filter -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label for="filter-type" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Jenis Bencana</label>
                <select id="filter-type" wire:model.live="type" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Semua jenis</option>
                    @foreach ($disasterTypes as $option)
                        <option value="{{ $option }}">{{ \App\Models\DisasterReport::disasterTypeLabel($option) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-status" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status</label>
                <select id="filter-status" wire:model.live="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-area" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Wilayah</label>
                <input id="filter-area" type="text" wire:model.live.debounce.400ms="area" placeholder="Cari alamat atau wilayah" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label for="filter-date-from" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kejadian Dari</label>
                <input id="filter-date-from" type="date" wire:model.live="dateFrom" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label for="filter-date-to" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kejadian Sampai</label>
                <input id="filter-date-to" type="date" wire:model.live="dateTo" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>
        </div>

        @if ($hasFilters)
            <div class="mt-3 flex items-center justify-between gap-3 text-xs text-slate-500">
                <span>{{ $reports->total() }} laporan sesuai filter.</span>
                <button type="button" wire:click="resetFilters" class="font-semibold text-blue-700 hover:text-blue-800">Hapus filter</button>
            </div>
        @endif
    </section>

    <!-- Peta Sebaran per Status -->
    <section
        class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 space-y-3"
        x-data="{
            map: null,
            layer: null,
            points: @js($points),
            colors: { menunggu: '#f59e0b', diterima: '#10b981', ditolak: '#f43f5e' },
            detailUrl: @js(route('admin.reports.verify', ['report' => '__ID__'])),
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
                this.map = L.map(container, { center: [-6.9175, 107.6191], zoom: 10, attributionControl: false, scrollWheelZoom: false });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(this.map);
                this.layer = L.layerGroup().addTo(this.map);
                this.draw(this.points);
                setTimeout(() => this.map && this.map.invalidateSize(), 250);
            },
            draw(points) {
                this.points = points || [];
                if (!this.map || !this.layer) return;
                this.layer.clearLayers();
                const bounds = [];
                this.points.forEach((point) => {
                    const marker = L.circleMarker([point.lat, point.lng], {
                        radius: 8,
                        color: '#ffffff',
                        weight: 2,
                        fillColor: this.colors[point.status] || '#64748b',
                        fillOpacity: 0.95,
                    });
                    const title = document.createElement('a');
                    title.href = this.detailUrl.replace('__ID__', point.id);
                    title.textContent = point.title;
                    title.className = 'font-semibold';
                    const status = document.createElement('div');
                    status.textContent = point.status_label;
                    const popup = document.createElement('div');
                    popup.append(title, status);
                    marker.bindPopup(popup);
                    marker.addTo(this.layer);
                    bounds.push([point.lat, point.lng]);
                });
                if (bounds.length) {
                    this.map.fitBounds(bounds, { padding: [30, 30], maxZoom: 15 });
                }
            }
        }"
        x-on:report-points-updated.window="draw($event.detail.points)"
    >
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="text-sm font-bold text-slate-800">Peta Sebaran Laporan</h2>
            <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-600">
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>Menunggu Verifikasi</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Diterima</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>Ditolak</span>
            </div>
        </div>
        <div wire:ignore class="rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
            <div x-ref="mapContainer" class="w-full" style="height: 300px; min-height: 300px;"></div>
        </div>
    </section>

    <!-- Daftar Laporan -->
    @if ($reports->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 p-10 text-center">
            <h2 class="text-sm font-semibold text-slate-800">{{ $hasFilters ? 'Tidak ada laporan yang sesuai filter' : 'Belum ada laporan masuk' }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $hasFilters ? 'Ubah atau hapus filter untuk melihat laporan lain.' : 'Laporan kejadian dari warga akan muncul di sini.' }}</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($reports as $report)
                @php
                    $badge = match ($report->status) {
                        \App\Models\DisasterReport::STATUS_ACCEPTED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        \App\Models\DisasterReport::STATUS_REJECTED => 'bg-rose-50 text-rose-700 border-rose-200',
                        default => 'bg-amber-50 text-amber-700 border-amber-200',
                    };
                    $thumbnail = $report->photos->first();
                @endphp
                <article wire:key="report-{{ $report->id }}" class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 flex gap-4">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden bg-slate-100 shrink-0 flex items-center justify-center text-slate-300">
                        @if ($thumbnail)
                            <img src="{{ $thumbnail->url }}" alt="Foto {{ $report->title }}" class="w-full h-full object-cover" loading="lazy">
                        @else
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full border {{ $badge }}">{{ $report->status_label }}</span>
                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $report->disaster_type_label }}</span>
                        </div>
                        <h2 class="mt-2 text-sm sm:text-base font-semibold text-slate-900 truncate">
                            <a href="{{ route('admin.reports.verify', $report) }}" wire:navigate class="hover:text-blue-700">{{ $report->title }}</a>
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-500 truncate">{{ $report->address }}</p>
                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-slate-500">
                            <span>Pelapor: {{ $report->reporter?->name ?? '-' }}</span>
                            <span>Kejadian: {{ $report->occurred_at?->translatedFormat('d M Y H:i') }}</span>
                            <span>Dikirim: {{ $report->created_at?->diffForHumans() }}</span>
                            <span>{{ $report->photos->count() }} foto</span>
                        </div>
                    </div>

                    <div class="shrink-0 self-center">
                        <a
                            href="{{ route('admin.reports.verify', $report) }}"
                            wire:navigate
                            class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-xs font-semibold transition {{ $report->isPending() ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-500/20' : 'border border-slate-200 text-slate-700 hover:bg-slate-50' }}"
                        >{{ $report->isPending() ? 'Verifikasi' : 'Lihat Detail' }}</a>
                    </div>
                </article>
            @endforeach
        </div>

        <div>
            {{ $reports->links() }}
        </div>
    @endif
</div>
