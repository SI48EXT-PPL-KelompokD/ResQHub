<div 
    x-data="publicShelterMap({
        userLat: @entangle('userLat'),
        userLng: @entangle('userLng'),
        userLocationName: @entangle('userLocationName'),
        markers: {{ json_encode($mapMarkers) }},
        selectedId: @entangle('selectedShelterId')
    })"
    x-init="initMap()"
    @user-location-updated.window="onUserLocationUpdated($event.detail)"
    @focus-shelter-marker.window="focusMarker($event.detail)"
    class="space-y-6 pb-12"
>
    <!-- Top Hero & Search Bar -->
    <div class="bg-gradient-to-b from-blue-700 via-blue-800 to-slate-900 text-white pt-8 pb-10 px-4 sm:px-6 lg:px-8 shadow-md">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- Hero Title & Badges -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 backdrop-blur-sm border border-white/20 mb-3 text-blue-100">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Informasi Siaga Tanggap Darurat Bencana</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white">
                        Pencarian Posko Evakuasi Terdekat
                    </h1>
                    <p class="text-xs sm:text-sm text-blue-100/90 mt-1.5 max-w-2xl leading-relaxed">
                        Cari lokasi posko pengungsian terdekat berdasarkan koordinat GPS Anda menggunakan kalkulasi jarak akurat (rumus Haversine). Pantau ketersediaan daya tampung posko secara realtime.
                    </p>
                </div>

                <!-- GPS Location Quick Card -->
                <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4 sm:max-w-xs w-full shadow-lg">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[11px] font-semibold text-blue-200 uppercase tracking-wider block">Lokasi Acuan Anda</span>
                            <span class="text-xs font-bold text-white truncate block max-w-[180px] mt-0.5" x-text="userLocationName">
                                {{ $userLocationName }}
                            </span>
                            <span class="text-[10px] text-blue-200/80 font-mono block mt-0.5">
                                {{ number_format($userLat, 4) }}, {{ number_format($userLng, 4) }}
                            </span>
                        </div>
                        <button 
                            type="button" 
                            @click="detectGPS()" 
                            :disabled="isLocating"
                            class="px-2.5 py-1.5 bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center space-x-1 cursor-pointer disabled:opacity-50 shrink-0"
                            title="Deteksi posisi saya menggunakan GPS browser"
                        >
                            <svg x-show="!isLocating" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z" />
                            </svg>
                            <svg x-show="isLocating" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isLocating ? 'Mendeteksi...' : 'GPS Saya'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search Inputs & Filters Box -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 text-slate-800 shadow-xl border border-slate-100 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    
                    <!-- 1. Search Query -->
                    <div class="lg:col-span-1">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Cari Posko / Lokasi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search"
                                placeholder="Nama posko, alamat, gedung..." 
                                class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            />
                        </div>
                    </div>

                    <!-- 2. Kejadian Bencana Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Kejadian Bencana
                        </label>
                        <select 
                            wire:model.live="disasterEventFilter"
                            class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                        >
                            <option value="">Semua Kejadian Bencana</option>
                            @foreach ($activeEvents as $event)
                                <option value="{{ $event->id }}">{{ $event->name }} ({{ $event->regency }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Radius Jarak (Haversine) -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Radius Jarak Terdekat
                        </label>
                        <select 
                            wire:model.live="radiusFilter"
                            class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                        >
                            <option value="">Semua Jarak (Diurutkan Terdekat)</option>
                            <option value="5">Maksimal 5 km</option>
                            <option value="10">Maksimal 10 km</option>
                            <option value="25">Maksimal 25 km</option>
                            <option value="50">Maksimal 50 km</option>
                        </select>
                    </div>

                    <!-- 4. Ganti Titik Acuan (Cari Alamat) -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Ubah Lokasi Acuan (Peta)
                        </label>
                        <div class="flex gap-1.5">
                            <input 
                                type="text" 
                                x-model="searchLocationQuery"
                                @keydown.enter.prevent="searchLocation()"
                                placeholder="Ketik nama kota / kecamatan..."
                                class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            />
                            <button 
                                type="button" 
                                @click="searchLocation()"
                                class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition cursor-pointer shrink-0"
                            >
                                Cari
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Status Kapasitas Pills Filter -->
                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider mr-1">Status Kapasitas:</span>
                        
                        <button 
                            type="button" 
                            wire:click="$set('capacityFilter', '')"
                            class="px-3 py-1.5 text-xs font-semibold rounded-xl border transition cursor-pointer {{ $capacityFilter === '' ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200' }}"
                        >
                            Semua ({{ $stats['total_shelters'] }})
                        </button>

                        <button 
                            type="button" 
                            wire:click="$set('capacityFilter', 'tersedia')"
                            class="px-3 py-1.5 text-xs font-semibold rounded-xl border transition cursor-pointer flex items-center space-x-1.5 {{ $capacityFilter === 'tersedia' ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}"
                        >
                            <span class="w-2 h-2 rounded-full {{ $capacityFilter === 'tersedia' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                            <span>Tersedia &lt;80% ({{ $stats['total_available'] }})</span>
                        </button>

                        <button 
                            type="button" 
                            wire:click="$set('capacityFilter', 'hampir_penuh')"
                            class="px-3 py-1.5 text-xs font-semibold rounded-xl border transition cursor-pointer flex items-center space-x-1.5 {{ $capacityFilter === 'hampir_penuh' ? 'bg-amber-500 text-white border-amber-500 shadow-xs' : 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' }}"
                        >
                            <span class="w-2 h-2 rounded-full {{ $capacityFilter === 'hampir_penuh' ? 'bg-white' : 'bg-amber-500' }}"></span>
                            <span>Hampir Penuh 80-99% ({{ $stats['total_nearly_full'] }})</span>
                        </button>

                        <button 
                            type="button" 
                            wire:click="$set('capacityFilter', 'penuh')"
                            class="px-3 py-1.5 text-xs font-semibold rounded-xl border transition cursor-pointer flex items-center space-x-1.5 {{ $capacityFilter === 'penuh' ? 'bg-red-600 text-white border-red-600 shadow-xs' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' }}"
                        >
                            <span class="w-2 h-2 rounded-full {{ $capacityFilter === 'penuh' ? 'bg-white' : 'bg-red-500' }}"></span>
                            <span>Penuh 100% ({{ $stats['total_full'] }})</span>
                        </button>
                    </div>

                    <!-- Reset Filter Shortcut -->
                    @if($search !== '' || $capacityFilter !== '' || $disasterEventFilter !== '' || $radiusFilter !== '')
                        <button 
                            type="button" 
                            wire:click="resetFilters"
                            class="text-xs text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center space-x-1 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Reset Semua Filter</span>
                        </button>
                    @endif
                </div>

            </div>

        </div>
    </div>

    <!-- Main Content Area: Split View (Interactive Map + Result Cards) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Info Bar & Sort -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 flex items-center space-x-2">
                    <span>Daftar Posko Terdekat</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                        {{ $shelters->count() }} Ditemukan
                    </span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Diurutkan berdasarkan jarak terdekat (rumus Haversine) dari titik acuan lokasi Anda.
                </p>
            </div>

            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-slate-500">Urutkan:</span>
                <select 
                    wire:model.live="sortBy"
                    class="py-1.5 px-3 text-xs font-medium bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600"
                >
                    <option value="distance">Jarak Terdekat (Default)</option>
                    <option value="capacity">Sisa Kapasitas Terbanyak</option>
                    <option value="name">Nama Posko (A-Z)</option>
                </select>
            </div>
        </div>

        <!-- 2-Column Responsive Grid: Map on Left, List on Right -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT COLUMN: Sticky Interactive Leaflet Map (5 cols) -->
            <div class="lg:col-span-5 lg:sticky lg:top-24 space-y-3">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-md overflow-hidden">
                    
                    <!-- Map Header -->
                    <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                🗺️
                            </span>
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Peta Sebaran Posko
                            </span>
                        </div>
                        <button 
                            type="button" 
                            @click="fitAllMarkers()"
                            class="text-[11px] font-semibold text-blue-600 hover:text-blue-800 cursor-pointer"
                        >
                            Pusatkan Semua Pin
                        </button>
                    </div>

                    <!-- Leaflet Container -->
                    <div wire:ignore class="relative">
                        <div x-ref="leafletContainer" class="w-full h-80 sm:h-96 lg:h-[460px] z-0 bg-slate-100"></div>
                        
                        <!-- Floating Map Legend -->
                        <div class="absolute bottom-3 left-3 right-3 z-10 pointer-events-none">
                            <div class="bg-white/95 backdrop-blur-md px-3 py-2 rounded-xl border border-slate-200 shadow-md flex flex-wrap items-center justify-between gap-2 text-[10px] pointer-events-auto">
                                <div class="flex items-center space-x-1">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                                    <span class="text-slate-700 font-medium">&lt;80% Tersedia</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span>
                                    <span class="text-slate-700 font-medium">80-99% Hampir</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span>
                                    <span class="text-slate-700 font-medium">100% Penuh</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span>
                                    <span class="text-slate-700 font-medium">Posisi Anda</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- RIGHT COLUMN: Shelter Cards List (7 cols) -->
            <div class="lg:col-span-7 space-y-4">
                
                @forelse($shelters as $shelter)
                    <div 
                        wire:key="shelter-card-{{ $shelter->id }}"
                        class="bg-white rounded-2xl border transition duration-200 shadow-sm hover:shadow-md overflow-hidden {{ $selectedShelterId === $shelter->id ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-slate-200' }}"
                    >
                        <div class="p-5 space-y-3.5">
                            
                            <!-- Card Header: Name, Distance & Status Badge -->
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                <div class="flex-1">
                                    <!-- Event Tag -->
                                    <span class="text-[11px] font-semibold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-md border border-blue-100 inline-block mb-1.5">
                                        {{ $shelter->disasterEvent?->name ?? 'Tanggap Bencana' }}
                                    </span>
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-blue-600 transition leading-snug">
                                        {{ $shelter->name }}
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-1 flex items-start space-x-1.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>{{ $shelter->address }}</span>
                                    </p>
                                </div>

                                <!-- Jarak & Status Badge -->
                                <div class="flex sm:flex-col items-center sm:items-end justify-between gap-1.5 shrink-0">
                                    <!-- Haversine Distance Badge -->
                                    <span class="inline-flex items-center space-x-1 px-3 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs">
                                        <span>📍</span>
                                        <span>{{ $shelter->formatted_distance }}</span>
                                    </span>

                                    <!-- Capacity Status Badge -->
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $shelter->capacity_badge_classes }}">
                                        {{ $shelter->capacity_status_label }}
                                    </span>
                                </div>
                            </div>

                            <!-- Kapasitas & Progress Bar Okupansi -->
                            <div class="bg-slate-50/80 p-3 rounded-xl border border-slate-200/80 space-y-2">
                                <div class="flex items-center justify-between text-xs font-semibold">
                                    <span class="text-slate-600">Kapasitas Daya Tampung:</span>
                                    <span class="text-slate-900">
                                        {{ number_format($shelter->occupancy) }} / {{ number_format($shelter->capacity) }} jiwa 
                                        <span class="text-slate-500 font-normal">({{ $shelter->occupancy_percentage }}%)</span>
                                    </span>
                                </div>

                                <!-- Progress Bar Color-coded -->
                                <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                                    <div 
                                        class="h-2.5 rounded-full transition-all duration-500 {{ $shelter->capacity_status === 'penuh' ? 'bg-red-500' : ($shelter->capacity_status === 'hampir_penuh' ? 'bg-amber-500' : 'bg-emerald-500') }}" 
                                        style="width: {{ min(100, $shelter->occupancy_percentage) }}%"
                                    ></div>
                                </div>

                                <div class="flex items-center justify-between text-[11px] text-slate-500 pt-0.5">
                                    <span>
                                        @if($shelter->remaining_capacity > 0)
                                            ✅ Sisa daya tampung: <strong class="text-emerald-700 font-semibold">{{ $shelter->remaining_capacity }} jiwa</strong>
                                        @else
                                            ⛔ <strong class="text-red-700 font-semibold">Kapasitas Penuh</strong> (0 jiwa tersedia)
                                        @endif
                                    </span>
                                    @if($shelter->contact)
                                        <a 
                                            href="tel:{{ $shelter->contact }}" 
                                            class="inline-flex items-center space-x-1 text-blue-600 hover:text-blue-800 font-semibold"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <span>{{ $shelter->contact }}</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Card Action Buttons -->
                            <div class="flex items-center justify-between pt-1 gap-2">
                                <button 
                                    type="button" 
                                    wire:click="selectShelter({{ $shelter->id }})"
                                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>Lihat di Peta</span>
                                </button>

                                <!-- Tombol "Arahkan" (Google Maps Directions) Acceptance Criteria 2 -->
                                <a 
                                    href="{{ $shelter->google_maps_url }}" 
                                    target="_blank" 
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center space-x-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs hover:shadow transition cursor-pointer"
                                    title="Buka rute navigasi Google Maps langsung ke posko ini"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                    <span>Arahkan ke Posko</span>
                                </a>
                            </div>

                        </div>
                    </div>
                @empty
                    <!-- Empty State -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-12 text-center space-y-4 shadow-sm">
                        <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mx-auto text-2xl">
                            🔍
                        </div>
                        <div class="max-w-md mx-auto">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">
                                Tidak Ada Posko yang Sesuai
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                                Tidak ditemukan posko evakuasi yang sesuai dengan kriteria pencarian atau radius jarak dari titik lokasi Anda saat ini.
                            </p>
                        </div>
                        <button 
                            type="button" 
                            wire:click="resetFilters"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition cursor-pointer"
                        >
                            Tampilkan Semua Posko
                        </button>
                    </div>
                @endforelse

            </div>

        </div>

    </div>

    <!-- Alpine.js Map Component Script -->
    <script>
        function publicShelterMap(config) {
            return {
                userLat: config.userLat,
                userLng: config.userLng,
                userLocationName: config.userLocationName,
                markersData: config.markers,
                selectedId: config.selectedId,
                map: null,
                leafletMarkers: {},
                userMarker: null,
                isLocating: false,
                searchLocationQuery: '',

                initMap() {
                    this.$nextTick(() => {
                        if (!this.$refs.leafletContainer || typeof L === 'undefined') return;

                        // Inisialisasi Leaflet
                        this.map = L.map(this.$refs.leafletContainer, {
                            zoomControl: true,
                            scrollWheelZoom: true
                        }).setView([this.userLat, this.userLng], 12);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(this.map);

                        this.renderMarkers();
                    });
                },

                renderMarkers() {
                    if (!this.map) return;

                    // Bersihkan marker lama
                    Object.values(this.leafletMarkers).forEach(m => this.map.removeLayer(m));
                    this.leafletMarkers = {};
                    if (this.userMarker) this.map.removeLayer(this.userMarker);

                    // Marker Titik Lokasi Pengguna (Pulsing Blue)
                    const userIcon = L.divIcon({
                        className: 'user-pos-marker',
                        html: '<div class="pulsing-user-marker"></div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    });

                    this.userMarker = L.marker([this.userLat, this.userLng], { icon: userIcon })
                        .addTo(this.map)
                        .bindPopup(`
                            <div class="p-2 text-xs">
                                <strong class="text-blue-700 block">📍 Posisi Acuan Anda</strong>
                                <span class="text-slate-600">${this.userLocationName}</span>
                            </div>
                        `);

                    // Marker Tiap Posko Evakuasi
                    this.markersData.forEach(s => {
                        let pinColor = '#10b981'; // green (<80%)
                        if (s.capacity_status === 'hampir_penuh') pinColor = '#f59e0b'; // amber
                        if (s.capacity_status === 'penuh') pinColor = '#ef4444'; // red
                        if (s.capacity_status === 'ditutup') pinColor = '#64748b'; // slate

                        const iconSvg = `
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="34" height="34">
                                <path fill="${pinColor}" stroke="#ffffff" stroke-width="1.5" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                        `;

                        const customIcon = L.divIcon({
                            className: 'shelter-marker-icon',
                            html: iconSvg,
                            iconSize: [34, 34],
                            iconAnchor: [17, 34],
                            popupAnchor: [0, -32]
                        });

                        const popupHtml = `
                            <div class="p-3 text-xs max-w-xs space-y-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold ${s.badge_classes}">${s.status_label}</span>
                                <h4 class="font-bold text-sm text-slate-900 leading-snug mt-1">${s.name}</h4>
                                <p class="text-slate-500 text-[11px]">${s.address}</p>
                                <div class="bg-slate-50 p-2 rounded-lg border border-slate-200 text-[11px] space-y-1">
                                    <div class="flex justify-between">
                                        <span>Jarak:</span>
                                        <strong class="text-blue-700">📍 ${s.formatted_distance}</strong>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Okupansi:</span>
                                        <strong>${s.occupancy} / ${s.capacity} jiwa (${s.percentage}%)</strong>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Sisa Kuota:</span>
                                        <strong class="${s.remaining > 0 ? 'text-emerald-700' : 'text-red-700'}">${s.remaining} jiwa</strong>
                                    </div>
                                </div>
                                <div class="pt-2">
                                    <a href="${s.google_maps_url}" target="_blank" class="block text-center py-1.5 px-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-xs shadow-xs">
                                        Arahkan Navigasi ➔
                                    </a>
                                </div>
                            </div>
                        `;

                        const marker = L.marker([s.lat, s.lng], { icon: customIcon })
                            .addTo(this.map)
                            .bindPopup(popupHtml);

                        this.leafletMarkers[s.id] = marker;
                    });
                },

                detectGPS() {
                    if (!navigator.geolocation) {
                        alert('Browser Anda tidak mendukung layanan geolokasi GPS.');
                        return;
                    }

                    this.isLocating = true;
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            this.$wire.setUserLocation(lat, lng, 'Lokasi GPS Perangkat');
                            this.isLocating = false;
                        },
                        (error) => {
                            this.isLocating = false;
                            alert('Gagal mendeteksi lokasi GPS: ' + error.message);
                        },
                        { timeout: 10000, enableHighAccuracy: true }
                    );
                },

                async searchLocation() {
                    const q = this.searchLocationQuery.trim();
                    if (!q) return;

                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&countrycodes=id&limit=1`);
                        const data = await res.json();
                        if (data && data.length > 0) {
                            const lat = parseFloat(data[0].lat);
                            const lng = parseFloat(data[0].lon);
                            const name = data[0].display_name.split(',')[0];
                            this.$wire.setUserLocation(lat, lng, name);
                            this.searchLocationQuery = '';
                        } else {
                            alert('Lokasi tidak ditemukan. Coba ketikkan nama kecamatan atau kota yang lebih spesifik.');
                        }
                    } catch (e) {
                        alert('Gagal mencari lokasi geocoding.');
                    }
                },

                onUserLocationUpdated(detail) {
                    this.userLat = detail.lat;
                    this.userLng = detail.lng;
                    this.userLocationName = detail.name;
                    this.renderMarkers();
                    if (this.map) {
                        this.map.flyTo([detail.lat, detail.lng], 13);
                    }
                },

                focusMarker(detail) {
                    if (!this.map) return;
                    this.map.flyTo([detail.lat, detail.lng], 15);
                    const marker = this.leafletMarkers[detail.id];
                    if (marker) {
                        setTimeout(() => marker.openPopup(), 400);
                    }
                },

                fitAllMarkers() {
                    if (!this.map) return;
                    const group = L.featureGroup([
                        this.userMarker,
                        ...Object.values(this.leafletMarkers)
                    ].filter(Boolean));
                    this.map.fitBounds(group.getBounds().pad(0.15));
                }
            };
        }
    </script>
</div>
