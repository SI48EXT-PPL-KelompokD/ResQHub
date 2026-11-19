<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex items-start gap-3 min-w-0">
        <a href="{{ $isEdit ? route('admin.warnings.show', $warningId) : route('admin.warnings.index') }}" wire:navigate class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-white border border-transparent hover:border-slate-200 transition" title="Kembali">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">{{ $isEdit ? 'Ubah Peringatan Dini' : 'Buat Peringatan Dini' }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $isEdit
                    ? 'Naikkan atau turunkan tingkat, perluas wilayah, ubah instruksi, atau perpanjang masa berlaku. Setiap perubahan tercatat di riwayat pembaruan.'
                    : 'Simpan sebagai draf untuk ditinjau, atau terbitkan langsung agar warga di wilayah sasaran menerima notifikasi.' }}
            </p>
        </div>
    </div>

    <!-- Informasi Peringatan -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-4">
        <h2 class="text-sm font-bold text-slate-800">Informasi Peringatan</h2>

        <div>
            <label for="title" class="block text-xs font-semibold text-slate-600 mb-1">Judul Peringatan <span class="text-red-500">*</span></label>
            <input id="title" type="text" wire:model="title" maxlength="255" placeholder="Contoh: Potensi banjir luapan Sungai Citarum" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
            @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="hazard_type" class="block text-xs font-semibold text-slate-600 mb-1">Jenis Ancaman <span class="text-red-500">*</span></label>
                <select id="hazard_type" wire:model="hazard_type" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Pilih jenis ancaman</option>
                    @foreach ($hazardTypes as $type)
                        <option value="{{ $type }}">{{ \App\Models\DisasterReport::disasterTypeLabel($type) }}</option>
                    @endforeach
                </select>
                @error('hazard_type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <span class="block text-xs font-semibold text-slate-600 mb-1">Tingkat Peringatan <span class="text-red-500">*</span></span>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($levels as $option)
                        @php
                            $checked = match ($option) {
                                'awas' => 'peer-checked:bg-red-600 peer-checked:border-red-600 peer-checked:text-white',
                                'siaga' => 'peer-checked:bg-orange-500 peer-checked:border-orange-500 peer-checked:text-white',
                                default => 'peer-checked:bg-yellow-300 peer-checked:border-yellow-300 peer-checked:text-yellow-900',
                            };
                        @endphp
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="level" value="{{ $option }}" class="peer sr-only">
                            <span class="block text-center px-3 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 transition {{ $checked }}">{{ \App\Models\EarlyWarning::levelLabel($option) }}</span>
                        </label>
                    @endforeach
                </div>
                @error('level') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="instruction" class="block text-xs font-semibold text-slate-600 mb-1">Instruksi untuk Warga <span class="text-red-500">*</span></label>
            <textarea id="instruction" wire:model="instruction" rows="4" maxlength="2000" placeholder="Contoh: Amankan dokumen penting, siapkan tas siaga, dan segera menuju posko terdekat bila air naik." class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600"></textarea>
            @error('instruction') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="starts_at" class="block text-xs font-semibold text-slate-600 mb-1">Mulai Berlaku <span class="text-red-500">*</span></label>
                <input id="starts_at" type="datetime-local" wire:model="starts_at" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                @error('starts_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="ends_at" class="block text-xs font-semibold text-slate-600 mb-1">Berlaku Sampai <span class="text-red-500">*</span></label>
                <input id="ends_at" type="datetime-local" wire:model="ends_at" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                @error('ends_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="disaster_event_id" class="block text-xs font-semibold text-slate-600 mb-1">Kejadian Terkait</label>
            <select id="disaster_event_id" wire:model="disaster_event_id" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="">Tidak dikaitkan (opsional)</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}">{{ $event->name }}{{ $event->regency ? ' - '.$event->regency : '' }}</option>
                @endforeach
            </select>
            @error('disaster_event_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </section>

    <!-- Wilayah Sasaran -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-4">
        <div>
            <h2 class="text-sm font-bold text-slate-800">Wilayah Sasaran <span class="text-red-500">*</span></h2>
            <p class="text-xs text-slate-500 mt-0.5">Pilih kabupaten/kota, tandai radius dari titik di peta, atau keduanya. Notifikasi dikirim ke warga yang terdaftar di kabupaten/kota sasaran; radius ditampilkan sebagai area di peta.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach ($regencies as $regency)
                <label class="flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 text-sm text-slate-700 hover:bg-slate-50 cursor-pointer">
                    <input type="checkbox" wire:model="target_regencies" value="{{ $regency }}" class="rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                    <span>{{ $regency }}</span>
                </label>
            @endforeach
        </div>
        @error('target_regencies') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        @error('target_regencies.*') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 cursor-pointer">
            <input type="checkbox" wire:model.live="useRadius" class="rounded border-slate-300 text-blue-600 focus:ring-blue-600">
            Tandai area radius dari titik di peta
        </label>

        <div
            x-data="{
                map: null,
                marker: null,
                circle: null,
                lat: $wire.entangle('center_lat'),
                lng: $wire.entangle('center_lng'),
                radius: $wire.entangle('radius_km'),
                show: $wire.entangle('useRadius'),
                init() {
                    this.setupMap();
                    this.$watch('show', (value) => { if (value) setTimeout(() => this.map && this.map.invalidateSize(), 150); });
                    this.$watch('radius', () => this.drawCircle());
                },
                hasPoint() {
                    return this.lat !== null && this.lat !== '' && this.lng !== null && this.lng !== '';
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
                    const center = this.hasPoint() ? [parseFloat(this.lat), parseFloat(this.lng)] : [-6.9935, 107.625];
                    this.map = L.map(container, { center: center, zoom: this.hasPoint() ? 12 : 10, attributionControl: false });
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(this.map);
                    if (this.hasPoint()) {
                        this.placeMarker(center[0], center[1]);
                    }
                    this.map.on('click', (e) => this.setPoint(e.latlng.lat, e.latlng.lng));
                    setTimeout(() => this.map && this.map.invalidateSize(), 250);
                },
                placeMarker(lat, lng) {
                    if (!this.marker) {
                        this.marker = L.marker([lat, lng], { draggable: true, autoPan: true }).addTo(this.map);
                        this.marker.on('dragend', (e) => {
                            const pos = e.target.getLatLng();
                            this.setPoint(pos.lat, pos.lng, false);
                        });
                    } else {
                        this.marker.setLatLng([lat, lng]);
                    }
                    this.drawCircle();
                },
                setPoint(lat, lng, moveMarker = true) {
                    this.lat = Number(parseFloat(lat).toFixed(7));
                    this.lng = Number(parseFloat(lng).toFixed(7));
                    if (moveMarker) {
                        this.placeMarker(this.lat, this.lng);
                    } else {
                        this.drawCircle();
                    }
                },
                drawCircle() {
                    if (!this.map || !this.hasPoint()) return;
                    const km = parseFloat(this.radius);
                    if (!km || km <= 0) {
                        if (this.circle) { this.map.removeLayer(this.circle); this.circle = null; }
                        return;
                    }
                    const center = [parseFloat(this.lat), parseFloat(this.lng)];
                    if (!this.circle) {
                        this.circle = L.circle(center, { radius: km * 1000, color: '#dc2626', fillColor: '#f87171', fillOpacity: 0.2, weight: 2 }).addTo(this.map);
                    } else {
                        this.circle.setLatLng(center);
                        this.circle.setRadius(km * 1000);
                    }
                }
            }"
            x-show="show"
            x-cloak
            class="space-y-3"
        >
            <p class="text-xs text-slate-500">Klik peta atau geser pin untuk menentukan titik pusat, lalu isi radius dalam kilometer.</p>
            <div wire:ignore class="rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                <div x-ref="mapContainer" class="w-full" style="height: 300px; min-height: 300px;"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Latitude Pusat</span>
                    <span class="font-mono text-slate-700" x-text="lat ?? 'Belum ditentukan'"></span>
                </div>
                <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Longitude Pusat</span>
                    <span class="font-mono text-slate-700" x-text="lng ?? 'Belum ditentukan'"></span>
                </div>
                <div>
                    <label for="radius_km" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Radius (km)</label>
                    <input id="radius_km" type="number" min="1" max="500" x-model.number="radius" placeholder="Contoh: 10" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>
            @error('center_lat') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @if (! $errors->has('center_lat'))
                @error('center_lng') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @endif
            @error('radius_km') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </section>

    @if ($isEdit)
        <!-- Catatan Pembaruan -->
        <section class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-2">
            <label for="note" class="block text-sm font-bold text-slate-800">Catatan Pembaruan</label>
            <p class="text-xs text-slate-500">Opsional. Jelaskan alasan perubahan, misalnya kenaikan debit air atau perpanjangan masa berlaku.</p>
            <textarea id="note" wire:model="note" rows="3" maxlength="1000" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600"></textarea>
            @error('note') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </section>
    @endif

    <!-- Aksi -->
    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
        <a href="{{ $isEdit ? route('admin.warnings.show', $warningId) : route('admin.warnings.index') }}" wire:navigate class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Batal</a>
        @if ($isEdit)
            <button type="button" wire:click="update" wire:loading.attr="disabled" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm shadow-blue-500/20 disabled:opacity-60 transition">Simpan Perubahan</button>
        @else
            <button type="button" wire:click="saveDraft" wire:loading.attr="disabled" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-blue-200 bg-blue-50 text-sm font-semibold text-blue-700 hover:bg-blue-100 disabled:opacity-60 transition">Simpan sebagai Draf</button>
            <button type="button" wire:click="publish" wire:loading.attr="disabled" wire:confirm="Terbitkan peringatan sekarang? Warga di wilayah sasaran akan langsung menerima notifikasi." class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow-sm shadow-red-500/20 disabled:opacity-60 transition">Terbitkan Sekarang</button>
        @endif
    </div>
</div>
