<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.reports.index') }}" wire:navigate class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-white border border-transparent hover:border-slate-200 transition" title="Kembali ke daftar laporan">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Buat Laporan Kejadian</h1>
            <p class="text-sm text-slate-500 mt-1">Laporan akan diverifikasi Admin Pusdalops sebelum ditindaklanjuti.</p>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">

        <!-- Informasi Kejadian -->
        <section class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-bold text-slate-800">Informasi Kejadian</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="disaster_type" class="block text-xs font-semibold text-slate-600 mb-1">Jenis Bencana <span class="text-red-500">*</span></label>
                    <select id="disaster_type" wire:model="disaster_type" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">Pilih jenis bencana</option>
                        @foreach ($disasterTypes as $type)
                            <option value="{{ $type }}">{{ \App\Models\DisasterReport::disasterTypeLabel($type) }}</option>
                        @endforeach
                    </select>
                    @error('disaster_type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="occurred_at" class="block text-xs font-semibold text-slate-600 mb-1">Waktu Kejadian <span class="text-red-500">*</span></label>
                    <input id="occurred_at" type="datetime-local" wire:model="occurred_at" max="{{ now()->format('Y-m-d\TH:i') }}" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('occurred_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="title" class="block text-xs font-semibold text-slate-600 mb-1">Judul Laporan <span class="text-red-500">*</span></label>
                <input id="title" type="text" wire:model="title" maxlength="255" placeholder="Contoh: Banjir setinggi 1 meter di Jl. Raya Dayeuhkolot" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi Kondisi <span class="text-red-500">*</span></label>
                <textarea id="description" wire:model="description" rows="4" maxlength="2000" placeholder="Jelaskan kondisi di lokasi, akses jalan, dan kebutuhan mendesak." class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600"></textarea>
                @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="sm:w-1/2">
                <label for="estimated_affected" class="block text-xs font-semibold text-slate-600 mb-1">Perkiraan Jumlah Terdampak (jiwa)</label>
                <input id="estimated_affected" type="number" min="0" wire:model="estimated_affected" placeholder="Opsional" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                @error('estimated_affected') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </section>

        <!-- Lokasi Kejadian (Peta Leaflet) -->
        <section
            class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-4"
            x-data="{
                map: null,
                marker: null,
                lat: $wire.entangle('lat'),
                lng: $wire.entangle('lng'),
                locating: false,
                geoError: '',
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
                    const hasPoint = this.lat !== null && this.lat !== '' && this.lng !== null && this.lng !== '';
                    const center = hasPoint ? [parseFloat(this.lat), parseFloat(this.lng)] : [-6.9935, 107.625];

                    this.map = L.map(container, { center: center, zoom: hasPoint ? 15 : 12, attributionControl: false });
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(this.map);

                    if (hasPoint) {
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
                },
                setPoint(lat, lng, moveMarker = true) {
                    this.lat = Number(parseFloat(lat).toFixed(7));
                    this.lng = Number(parseFloat(lng).toFixed(7));
                    if (moveMarker) this.placeMarker(this.lat, this.lng);
                },
                useMyLocation() {
                    this.geoError = '';
                    if (!navigator.geolocation) {
                        this.geoError = 'Peramban tidak mendukung GPS. Tentukan titik langsung di peta.';
                        return;
                    }
                    this.locating = true;
                    navigator.geolocation.getCurrentPosition((pos) => {
                        this.locating = false;
                        this.setPoint(pos.coords.latitude, pos.coords.longitude);
                        this.map.setView([this.lat, this.lng], 16);
                    }, () => {
                        this.locating = false;
                        this.geoError = 'Lokasi tidak dapat diambil. Izinkan akses lokasi atau klik titik di peta.';
                    }, { enableHighAccuracy: true, timeout: 10000 });
                }
            }"
        >
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Lokasi Kejadian <span class="text-red-500">*</span></h2>
                    <p class="text-xs text-slate-500 mt-0.5">Klik peta atau geser pin untuk menandai titik lokasi.</p>
                </div>
                <button type="button" @click="useMyLocation()" :disabled="locating" class="inline-flex items-center justify-center space-x-2 px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-60 transition">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span x-text="locating ? 'Mencari lokasi...' : 'Gunakan lokasi saya'"></span>
                </button>
            </div>

            <div wire:ignore class="rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                <div x-ref="mapContainer" class="w-full" style="height: 280px; min-height: 280px;"></div>
            </div>

            <p x-show="geoError" x-cloak x-text="geoError" class="text-xs text-amber-600"></p>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Latitude</span>
                    <span class="font-mono text-slate-700" x-text="lat ?? 'Belum ditentukan'"></span>
                </div>
                <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Longitude</span>
                    <span class="font-mono text-slate-700" x-text="lng ?? 'Belum ditentukan'"></span>
                </div>
            </div>
            @error('lat') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @if (! $errors->has('lat'))
                @error('lng') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @endif

            <div>
                <label for="address" class="block text-xs font-semibold text-slate-600 mb-1">Alamat / Patokan Lokasi <span class="text-red-500">*</span></label>
                <input id="address" type="text" wire:model="address" maxlength="255" placeholder="Nama jalan, RT/RW, desa/kelurahan, kecamatan" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                @error('address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </section>

        <!-- Foto Kejadian -->
        <section class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-4">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Foto Kejadian</h2>
                <p class="text-xs text-slate-500 mt-0.5">Opsional, maksimal {{ $maxPhotos }} foto JPG/PNG, masing-masing 2 MB.</p>
            </div>

            <div class="grid grid-cols-3 gap-3">
                @foreach ($photos as $index => $photo)
                    <div wire:key="photo-{{ $index }}" class="relative aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                        @if (method_exists($photo, 'isPreviewable') && $photo->isPreviewable())
                            <img src="{{ $photo->temporaryUrl() }}" alt="Pratinjau foto {{ $index + 1 }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-[11px] text-slate-500 p-2 text-center">{{ $photo->getClientOriginalName() }}</div>
                        @endif
                        <button type="button" wire:click="removePhoto({{ $index }})" class="absolute top-1.5 right-1.5 w-7 h-7 rounded-full bg-white/95 text-rose-600 shadow-sm flex items-center justify-center hover:bg-rose-50" title="Hapus foto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endforeach

                @if (count($photos) < $maxPhotos)
                    <label class="aspect-square rounded-xl border-2 border-dashed border-slate-200 hover:border-blue-400 hover:bg-blue-50/40 flex flex-col items-center justify-center text-slate-400 hover:text-blue-600 cursor-pointer transition">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="text-[11px] font-semibold mt-1">Tambah foto</span>
                        <input type="file" wire:model="newPhotos" multiple accept="image/jpeg,image/png" class="sr-only">
                    </label>
                @endif
            </div>

            <div wire:loading wire:target="newPhotos" class="text-xs text-blue-600">Mengunggah foto...</div>
            @error('photos') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @error('photos.*') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @error('newPhotos.*') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </section>

        <!-- Aksi -->
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
            <a href="{{ route('admin.reports.index') }}" wire:navigate class="inline-flex justify-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Batal</a>
            <button type="submit" wire:loading.attr="disabled" wire:target="save,newPhotos" class="inline-flex justify-center items-center space-x-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white text-sm font-semibold shadow-sm shadow-blue-500/20 transition">
                <span wire:loading.remove wire:target="save">Kirim Laporan</span>
                <span wire:loading wire:target="save">Mengirim...</span>
            </button>
        </div>
    </form>
</div>
