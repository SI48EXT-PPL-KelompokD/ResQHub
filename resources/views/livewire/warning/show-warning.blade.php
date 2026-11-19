<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="flex items-start gap-3 min-w-0">
            <a href="{{ route('admin.warnings.index') }}" wire:navigate class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-white border border-transparent hover:border-slate-200 transition" title="Kembali ke daftar peringatan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    @include('livewire.warning.partials.badges', ['warning' => $warning])
                </div>
                <h1 class="mt-2 text-xl sm:text-2xl font-bold tracking-tight text-slate-900 break-words">{{ $warning->title }}</h1>
                <p class="text-sm text-slate-500 mt-1">Dibuat oleh {{ $warning->issuer?->name ?? '-' }} pada {{ $warning->created_at?->translatedFormat('d M Y H:i') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 shrink-0">
            @can('update', $warning)
                <a href="{{ route('admin.warnings.edit', $warning) }}" wire:navigate class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Ubah</a>
            @endcan
            @can('publish', $warning)
                <button type="button" wire:click="publish" wire:confirm="Terbitkan peringatan ini? Warga di wilayah sasaran akan langsung menerima notifikasi." class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-semibold shadow-sm shadow-red-500/20 transition">Terbitkan</button>
            @endcan
            @can('delete', $warning)
                <button type="button" wire:click="deleteDraft" wire:confirm="Hapus draf peringatan ini secara permanen?" class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition">Hapus Draf</button>
            @endcan
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

    @if ($warning->status === \App\Models\EarlyWarning::STATUS_REVOKED)
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm text-rose-800">
            <span class="font-semibold">Peringatan dicabut.</span> Alasan pencabutan: {{ $warning->revoke_reason ?? '-' }}
        </div>
    @elseif ($warning->status === \App\Models\EarlyWarning::STATUS_EXPIRED)
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700">
            Peringatan sudah melewati masa berlaku dan berstatus kedaluwarsa.
        </div>
    @endif

    <!-- Ringkasan -->
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4">
            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Warga Penerima Notifikasi</span>
            <span class="mt-1 block text-2xl font-bold text-slate-900">{{ number_format($recipientCount, 0, ',', '.') }}</span>
            <span class="text-[11px] text-slate-500">Warga terdaftar di kabupaten/kota sasaran</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4">
            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Mulai Berlaku</span>
            <span class="mt-1 block text-sm font-semibold text-slate-900">{{ $warning->starts_at?->translatedFormat('d M Y H:i') }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4">
            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Berlaku Sampai</span>
            <span class="mt-1 block text-sm font-semibold text-slate-900">{{ $warning->ends_at?->translatedFormat('d M Y H:i') }}</span>
        </div>
    </section>

    <!-- Instruksi -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-3">
        <h2 class="text-sm font-bold text-slate-800">Instruksi untuk Warga</h2>
        <p class="text-sm text-slate-700 whitespace-pre-line">{{ $warning->instruction }}</p>
        @if ($warning->disasterEvent)
            <p class="text-xs text-slate-500">Kejadian terkait: <span class="font-semibold text-slate-700">{{ $warning->disasterEvent->name }}</span></p>
        @endif
    </section>

    <!-- Wilayah Sasaran -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-3">
        <h2 class="text-sm font-bold text-slate-800">Wilayah Sasaran</h2>
        @if (! empty($warning->target_regencies))
            <div class="flex flex-wrap gap-2">
                @foreach ($warning->target_regencies as $regency)
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">{{ $regency }}</span>
                @endforeach
            </div>
        @else
            <p class="text-xs text-slate-500">Tidak ada kabupaten/kota sasaran.</p>
        @endif

        @if ($warning->radius_km && $warning->center_lat !== null && $warning->center_lng !== null)
            <p class="text-xs text-slate-500">Radius {{ $warning->radius_km }} km dari titik {{ $warning->center_lat }}, {{ $warning->center_lng }}.</p>
            <div
                wire:ignore
                class="rounded-xl overflow-hidden border border-slate-200 bg-slate-100"
                x-data="{
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
                        const center = [{{ $warning->center_lat }}, {{ $warning->center_lng }}];
                        const map = L.map(container, { center: center, zoom: 11, attributionControl: false, scrollWheelZoom: false });
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
                        L.marker(center).addTo(map);
                        const circle = L.circle(center, { radius: {{ $warning->radius_km }} * 1000, color: '#dc2626', fillColor: '#f87171', fillOpacity: 0.2, weight: 2 }).addTo(map);
                        setTimeout(() => { map.invalidateSize(); map.fitBounds(circle.getBounds(), { padding: [20, 20] }); }, 250);
                    }
                }"
            >
                <div x-ref="mapContainer" class="w-full" style="height: 280px; min-height: 280px;"></div>
            </div>
        @endif
    </section>

    @can('revoke', $warning)
        <!-- Cabut Peringatan -->
        <section class="bg-white rounded-2xl border border-rose-200 p-5 sm:p-6 space-y-3">
            <div>
                <h2 class="text-sm font-bold text-rose-700">Cabut Peringatan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Peringatan yang dicabut hilang dari tampilan warga, dan warga terdampak menerima pemberitahuan pencabutan.</p>
            </div>
            <div>
                <label for="revoke_reason" class="block text-xs font-semibold text-slate-600 mb-1">Alasan Pencabutan <span class="text-red-500">*</span></label>
                <textarea id="revoke_reason" wire:model="revoke_reason" rows="3" maxlength="255" placeholder="Contoh: Debit air sudah kembali normal." class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                @error('revoke_reason') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end">
                <button type="button" wire:click="revoke" wire:confirm="Cabut peringatan ini sekarang?" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold transition">Cabut Peringatan</button>
            </div>
        </section>
    @endcan

    <!-- Riwayat Pembaruan -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-4">
        <h2 class="text-sm font-bold text-slate-800">Riwayat Pembaruan</h2>
        @if ($history->isEmpty())
            <p class="text-sm text-slate-500">Belum ada pembaruan sejak peringatan dibuat.</p>
        @else
            <ol class="relative border-l border-slate-200 ml-2 space-y-4">
                @foreach ($history as $item)
                    <li class="ml-4">
                        <span class="absolute -left-1.5 mt-1.5 w-3 h-3 rounded-full border-2 border-white {{ $item['direction'] > 0 ? 'bg-red-500' : ($item['direction'] < 0 ? 'bg-emerald-500' : 'bg-slate-400') }}"></span>
                        <p class="text-sm font-semibold text-slate-800">
                            @if ($item['direction'] > 0)
                                Tingkat dinaikkan: {{ $item['old_level'] }} &rarr; {{ $item['new_level'] }}
                            @elseif ($item['direction'] < 0)
                                Tingkat diturunkan: {{ $item['old_level'] }} &rarr; {{ $item['new_level'] }}
                            @else
                                Peringatan diperbarui (tingkat tetap {{ $item['new_level'] }})
                            @endif
                        </p>
                        @if ($item['note'])
                            <p class="text-sm text-slate-600 mt-0.5">{{ $item['note'] }}</p>
                        @endif
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $item['by'] }} &middot; {{ $item['at']?->translatedFormat('d M Y H:i') }}</p>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>
</div>
