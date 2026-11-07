<div class="space-y-6">

    <!-- Toast Notification -->
    @if ($notification)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false; $wire.dismissNotification(); }, 4000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="fixed top-20 right-6 z-[110] max-w-sm w-full p-4 rounded-xl shadow-lg border {{ $notification['type'] === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800' }}"
        >
            <div class="flex items-start justify-between space-x-3">
                <div class="flex items-center space-x-2">
                    @if ($notification['type'] === 'error')
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    @endif
                    <p class="text-xs sm:text-sm font-semibold">{{ $notification['message'] }}</p>
                </div>
                <button wire:click="dismissNotification" class="text-slate-400 hover:text-slate-600 p-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    Posko Evakuasi
                </h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 font-mono">
                    PBI-04
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">
                Kelola data posko bencana, kapasitas tampung, koordinat lokasi, dan kontak narahubung darurat.
            </p>
        </div>

        <button 
            type="button"
            wire:click="openCreateModal"
            class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs hover:shadow-md transition duration-150 transform active:scale-98 cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>Tambah Posko</span>
        </button>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-4 sm:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            
            <!-- Search -->
            <div class="relative lg:col-span-2">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama posko, alamat, atau kontak..." 
                    class="w-full pl-10 pr-4 py-2 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                />
            </div>

            <!-- Event Filter -->
            <div>
                <select 
                    wire:model.live="eventFilter"
                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white text-slate-700 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                >
                    <option value="">Semua Kejadian Bencana</option>
                    @foreach ($activeEvents as $event)
                        <option value="{{ $event->id }}">{{ $event->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <select 
                    wire:model.live="statusFilter"
                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white text-slate-700 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                >
                    <option value="">Semua Status Posko</option>
                    <option value="aktif">Aktif</option>
                    <option value="penuh">Penuh</option>
                    <option value="ditutup">Ditutup</option>
                </select>
            </div>

        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-slate-700">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4 sm:px-6">Nama Posko & Lokasi</th>
                        <th class="py-3.5 px-4">Kejadian Bencana</th>
                        <th class="py-3.5 px-4">Kapasitas & Okupansi</th>
                        <th class="py-3.5 px-4">Kontak</th>
                        <th class="py-3.5 px-4">Status Posko</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse ($shelters as $shelter)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Nama Posko & Alamat -->
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex items-start space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900 leading-snug">
                                            {{ $shelter->name }}
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">
                                            {{ $shelter->address }}
                                        </p>
                                        <span class="inline-block text-[11px] text-slate-400 font-mono mt-0.5">
                                            📍 {{ number_format($shelter->lat, 4) }}, {{ number_format($shelter->lng, 4) }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Kejadian Bencana -->
                            <td class="py-4 px-4">
                                <div class="font-medium text-slate-900">
                                    {{ $shelter->disasterEvent?->name ?? '-' }}
                                </div>
                                <span class="text-[11px] text-slate-500 capitalize">
                                    {{ str_replace('_', ' ', $shelter->disasterEvent?->disaster_type ?? '') }}
                                </span>
                            </td>

                            <!-- Kapasitas & Okupansi -->
                            <td class="py-4 px-4">
                                <div class="space-y-1.5 min-w-[140px]">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-800">{{ $shelter->occupancy }} <span class="text-slate-400 font-normal">/ {{ $shelter->capacity }} jiwa</span></span>
                                        <span class="font-semibold text-[11px] text-slate-600">{{ $shelter->occupancy_percentage }}%</span>
                                    </div>
                                    <!-- Progress bar -->
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        @php
                                            $barColor = match ($shelter->capacity_status) {
                                                'ditutup' => 'bg-slate-400',
                                                'penuh' => 'bg-red-500',
                                                'hampir_penuh' => 'bg-amber-500',
                                                default => 'bg-emerald-500',
                                            };
                                        @endphp
                                        <div class="h-full {{ $barColor }} rounded-full transition-all duration-300" style="width: {{ min(100, $shelter->occupancy_percentage) }}%"></div>
                                    </div>
                                    <!-- Status Kapasitas Badge -->
                                    <div>
                                        @if ($shelter->capacity_status === 'tersedia')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Tersedia (&lt;80%)
                                            </span>
                                        @elseif ($shelter->capacity_status === 'hampir_penuh')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                Hampir Penuh (80-99%)
                                            </span>
                                        @elseif ($shelter->capacity_status === 'penuh')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-red-50 text-red-700 border border-red-200">
                                                Penuh (100%)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                Ditutup
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Kontak -->
                            <td class="py-4 px-4">
                                <span class="text-xs font-mono text-slate-700 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200/70 inline-block">
                                    {{ $shelter->contact }}
                                </span>
                            </td>

                            <!-- Status Posko -->
                            <td class="py-4 px-4">
                                @if ($shelter->status === 'aktif')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        Aktif
                                    </span>
                                @elseif ($shelter->status === 'penuh')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                                        Penuh
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                        Ditutup
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-4 sm:px-6 text-right">
                                <div class="inline-flex items-center space-x-1.5">
                                    <button 
                                        type="button"
                                        class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                        title="Detail Posko"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <p class="text-sm font-semibold text-slate-700">Belum ada posko evakuasi</p>
                                <p class="text-xs text-slate-400 mt-1">Gunakan tombol "Tambah Posko" di atas untuk mendaftarkan posko baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $shelters->links() }}
        </div>
    </div>

    <!-- Modal Form Tambah Posko (Smooth Stacking, Centering & Scrolling) -->
    <div 
        x-data="{ open: $wire.entangle('showCreateModal') }"
        x-show="open"
        x-cloak
        @keydown.escape.window="open = false; $wire.closeCreateModal()"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog" 
        aria-modal="true"
        aria-labelledby="modal-title"
    >
        <!-- Backdrop Blur (Silky Smooth Fade) -->
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm cursor-pointer" 
            @click="open = false; $wire.closeCreateModal()"
            aria-hidden="true"
        ></div>

        <!-- Centered & Scrollable Wrapper -->
        <div class="min-h-full flex items-center justify-center p-4 sm:p-6 relative z-10">
            <!-- Modal Dialog Card (Smooth Scale & Translate) -->
            <div 
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                class="w-full max-w-xl bg-white rounded-2xl text-left shadow-2xl border border-slate-200 overflow-hidden my-6"
            >
                <form wire:submit.prevent="store">
                    <div class="p-6">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900" id="modal-title">
                                    Tambah Posko Evakuasi Baru
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Posko akan didaftarkan dengan status <strong class="text-emerald-600">Aktif</strong> di bawah akun Anda.
                                </p>
                            </div>
                            <button 
                                type="button" 
                                @click="open = false; $wire.closeCreateModal()" 
                                class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <!-- Kejadian Bencana (Harus Aktif) -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Kejadian Bencana Terkait <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    wire:model="disaster_event_id" 
                                    required 
                                    class="w-full py-2.5 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition bg-slate-50 hover:bg-white focus:bg-white"
                                >
                                    <option value="">-- Pilih Kejadian Bencana Aktif --</option>
                                    @foreach ($activeEvents as $event)
                                        <option value="{{ $event->id }}">
                                            {{ $event->name }} ({{ ucfirst($event->disaster_type) }} - {{ $event->regency }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('disaster_event_id') <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <!-- Nama Posko -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Nama Posko <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    wire:model="name" 
                                    required 
                                    placeholder="Contoh: Posko Utama GOR Baleendah" 
                                    class="w-full py-2.5 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('name') <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <!-- Alamat Posko -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Alamat Lengkap <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    wire:model="address" 
                                    required 
                                    rows="2" 
                                    placeholder="Tuliskan nama jalan, kelurahan/desa, kecamatan..."
                                    class="w-full py-2.5 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                ></textarea>
                                @error('address') <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <!-- Koordinat (Lat & Lng) -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Latitude <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        step="0.0000001" 
                                        wire:model="lat" 
                                        required 
                                        placeholder="-6.9935" 
                                        class="w-full py-2.5 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition font-mono"
                                    />
                                    @error('lat') <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Longitude <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        step="0.0000001" 
                                        wire:model="lng" 
                                        required 
                                        placeholder="107.6250" 
                                        class="w-full py-2.5 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition font-mono"
                                    />
                                    @error('lng') <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Kapasitas & Kontak Row -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Kapasitas -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Kapasitas Tampung (Jiwa) <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        min="1" 
                                        wire:model="capacity" 
                                        required 
                                        placeholder="Contoh: 300" 
                                        class="w-full py-2.5 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                    />
                                    <span class="text-[11px] text-slate-400 mt-0.5 block">Kapasitas harus lebih dari 0 jiwa.</span>
                                    @error('capacity') <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                                </div>

                                <!-- Kontak Narahubung -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Kontak Narahubung <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        wire:model="contact" 
                                        required 
                                        placeholder="Contoh: 081234567802 (Pak Budi)" 
                                        class="w-full py-2.5 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                    />
                                    @error('contact') <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end space-x-2 border-t border-slate-100 rounded-b-2xl">
                        <button 
                            type="button" 
                            @click="open = false; $wire.closeCreateModal()"
                            class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center space-x-1.5 px-5 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition duration-150 disabled:opacity-50 cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="store">Simpan Posko</span>
                            <span wire:loading wire:target="store" class="inline-flex items-center space-x-1">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Menyimpan...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
