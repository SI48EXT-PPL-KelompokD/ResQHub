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

    <!-- Modul B: Kartu Ringkasan Status Kapasitas Posko (Interactive KPI Widgets) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        
        <!-- Total Posko Card -->
        <button 
            type="button"
            wire:click="setCapacityFilter('')"
            class="text-left p-4 rounded-2xl bg-white border transition-all duration-200 shadow-2xs hover:shadow-md cursor-pointer {{ $capacityFilter === '' ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-slate-200/80 hover:border-slate-300' }}"
            title="Tampilkan semua posko evakuasi"
        >
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Posko</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</span>
                <span class="text-xs text-slate-500">posko</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1 truncate">
                Total kuota: <span class="font-semibold text-slate-700">{{ number_format($stats['total_capacity']) }}</span> jiwa
            </p>
        </button>

        <!-- Posko Tersedia Card (<80%) -->
        <button 
            type="button"
            wire:click="setCapacityFilter('tersedia')"
            class="text-left p-4 rounded-2xl bg-white border transition-all duration-200 shadow-2xs hover:shadow-md cursor-pointer {{ $capacityFilter === 'tersedia' ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/20' : 'border-slate-200/80 hover:border-emerald-300' }}"
            title="Klik untuk filter posko yang masih tersedia (<80%)"
        >
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Tersedia (&lt;80%)</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-2xl font-bold text-emerald-600">{{ $stats['tersedia'] }}</span>
                <span class="text-xs text-slate-500">posko siap</span>
            </div>
            <p class="text-[11px] text-emerald-600/90 mt-1 truncate font-medium">
                Sisa kuota: {{ number_format($stats['total_remaining']) }} jiwa
            </p>
        </button>

        <!-- Posko Hampir Penuh Card (80-99%) -->
        <button 
            type="button"
            wire:click="setCapacityFilter('hampir_penuh')"
            class="text-left p-4 rounded-2xl bg-white border transition-all duration-200 shadow-2xs hover:shadow-md cursor-pointer {{ $capacityFilter === 'hampir_penuh' ? 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-50/20' : 'border-slate-200/80 hover:border-amber-300' }}"
            title="Klik untuk filter posko yang hampir penuh (80-99%)"
        >
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Hampir Penuh</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-2xl font-bold text-amber-600">{{ $stats['hampir_penuh'] }}</span>
                <span class="text-xs text-slate-500">posko waspada</span>
            </div>
            <p class="text-[11px] text-amber-600/90 mt-1 truncate font-medium">
                Okupansi 80% s.d. 99%
            </p>
        </button>

        <!-- Posko Penuh Card (100%) -->
        <button 
            type="button"
            wire:click="setCapacityFilter('penuh')"
            class="text-left p-4 rounded-2xl bg-white border transition-all duration-200 shadow-2xs hover:shadow-md cursor-pointer {{ $capacityFilter === 'penuh' ? 'border-red-500 ring-2 ring-red-500/20 bg-red-50/20' : 'border-slate-200/80 hover:border-red-300' }}"
            title="Klik untuk filter posko yang penuh (100%)"
        >
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-red-700 uppercase tracking-wider">Penuh (100%)</span>
                <span class="w-8 h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </span>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-2xl font-bold text-red-600">{{ $stats['penuh'] }}</span>
                <span class="text-xs text-slate-500">tidak menampung</span>
            </div>
            <p class="text-[11px] text-red-600/90 mt-1 truncate font-medium">
                Kapasitas maksimal terisi
            </p>
        </button>

    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-4 sm:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            
            <!-- Search -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama posko, alamat..." 
                    class="w-full pl-10 pr-4 py-2 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                />
            </div>

            <!-- Event Filter -->
            <div>
                <select 
                    wire:model.live="eventFilter"
                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white text-slate-700 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                >
                    <option value="">Semua Bencana</option>
                    @foreach ($activeEvents as $event)
                        <option value="{{ $event->id }}">{{ $event->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Capacity Status Filter (SCRUM-56) -->
            <div>
                <select 
                    wire:model.live="capacityFilter"
                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white text-slate-700 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                >
                    <option value="">Semua Kapasitas</option>
                    <option value="tersedia">🟢 Tersedia (&lt;80%)</option>
                    <option value="hampir_penuh">🟠 Hampir Penuh (80-99%)</option>
                    <option value="penuh">🔴 Penuh (100%)</option>
                    <option value="ditutup">⚪ Ditutup</option>
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

                            <!-- Kapasitas & Okupansi (SCRUM-56) -->
                            <td class="py-4 px-4">
                                <div class="space-y-1.5 min-w-[150px]">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-800">{{ number_format($shelter->occupancy) }} <span class="text-slate-400 font-normal">/ {{ number_format($shelter->capacity) }} jiwa</span></span>
                                        <span class="font-bold text-[11px] {{ $shelter->capacity_status === 'penuh' ? 'text-red-600' : ($shelter->capacity_status === 'hampir_penuh' ? 'text-amber-600' : 'text-emerald-600') }}">
                                            {{ $shelter->occupancy_percentage }}%
                                        </span>
                                    </div>
                                    <!-- Progress bar -->
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
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
                                    <!-- Status Kapasitas Badge & Sisa Kuota -->
                                    <div class="flex items-center justify-between gap-1 pt-0.5">
                                        @if ($shelter->capacity_status === 'tersedia')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span>
                                                Tersedia (&lt;80%)
                                            </span>
                                        @elseif ($shelter->capacity_status === 'hampir_penuh')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1 animate-pulse"></span>
                                                Hampir Penuh
                                            </span>
                                        @elseif ($shelter->capacity_status === 'penuh')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1 animate-ping"></span>
                                                Penuh (100%)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                Ditutup
                                            </span>
                                        @endif

                                        <span class="text-[10px] text-slate-400 font-medium">
                                            Sisa: <strong class="text-slate-600 font-semibold">{{ number_format($shelter->remaining_capacity) }}</strong>
                                        </span>
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
                                <div class="inline-flex items-center space-x-1">
                                    <!-- Tombol Update Okupansi Pengungsi -->
                                    <button 
                                        type="button"
                                        wire:click="openOccupancyModal({{ $shelter->id }})"
                                        class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                                        title="Update Jumlah Pengungsi Aktif"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </button>

                                    <!-- Tombol Detail Posko -->
                                    <button 
                                        type="button"
                                        class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition cursor-pointer"
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
        x-effect="if (open) { setTimeout(() => window.dispatchEvent(new CustomEvent('render-shelter-map')), 250); }"
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
                class="w-full max-w-2xl bg-white rounded-2xl text-left shadow-2xl border border-slate-200 overflow-hidden my-6"
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

                            <!-- Titik Koordinat & Peta Interaktif Leaflet (Nominatim Geocoding + Pin Picker) -->
                            <div 
                                x-data="shelterMapPicker()"
                                x-init="init()"
                                @render-shelter-map.window="setupMap()"
                                class="bg-slate-50/80 p-3.5 sm:p-4 rounded-2xl border border-slate-200/90 space-y-3"
                            >
                                <!-- Header Titik Peta -->
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </span>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider leading-none">
                                                Titik Lokasi Posko (Peta Leaflet) <span class="text-red-500">*</span>
                                            </label>
                                            <p class="text-[11px] text-slate-500 mt-0.5">Klik peta, geser pin, atau cari nama lokasi</p>
                                        </div>
                                    </div>

                                    <!-- Tombol Gunakan GPS Saya -->
                                    <button 
                                        type="button" 
                                        @click="getCurrentLocation()"
                                        :disabled="isLocating"
                                        class="inline-flex items-center justify-center space-x-1.5 px-2.5 py-1.5 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg border border-slate-200 shadow-2xs transition active:scale-95 disabled:opacity-50 cursor-pointer"
                                        title="Gunakan posisi GPS perangkat saat ini"
                                    >
                                        <template x-if="!isLocating">
                                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </template>
                                        <template x-if="isLocating">
                                            <svg class="animate-spin w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </template>
                                        <span x-text="isLocating ? 'Mendeteksi...' : 'GPS Saya'"></span>
                                    </button>
                                </div>

                                <!-- Kolom Pencarian Nama Lokasi (Nominatim OpenStreetMap) -->
                                <div class="relative">
                                    <div class="flex items-center space-x-1.5">
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            </div>
                                            <input 
                                                type="text" 
                                                x-model="searchQuery" 
                                                @keydown.enter.prevent="searchLocation()"
                                                placeholder="Cari nama tempat / jalan (misal: GOR Baleendah, Stadion Si Jalak Harupat)..." 
                                                class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                            />
                                        </div>
                                        <button 
                                            type="button" 
                                            @click="searchLocation()"
                                            :disabled="isSearching"
                                            class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold rounded-xl transition shadow-2xs flex items-center space-x-1 shrink-0 cursor-pointer disabled:opacity-50"
                                        >
                                            <template x-if="!isSearching">
                                                <span>Cari</span>
                                            </template>
                                            <template x-if="isSearching">
                                                <span class="inline-flex items-center space-x-1">
                                                    <svg class="animate-spin w-3 h-3 text-white" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <span>Mencari...</span>
                                                </span>
                                            </template>
                                        </button>
                                    </div>

                                    <!-- Dropdown Hasil Pencarian Tempat -->
                                    <div 
                                        x-show="searchResults.length > 0" 
                                        @click.away="searchResults = []"
                                        x-cloak
                                        x-transition
                                        class="absolute left-0 right-0 top-full mt-1 z-30 bg-white rounded-xl shadow-xl border border-slate-200 max-h-48 overflow-y-auto divide-y divide-slate-100"
                                    >
                                        <template x-for="(item, idx) in searchResults" :key="idx">
                                            <button 
                                                type="button"
                                                @click="selectLocation(item)"
                                                class="w-full px-3 py-2 text-left hover:bg-blue-50/80 transition flex items-start space-x-2.5 cursor-pointer"
                                            >
                                                <span class="text-blue-600 mt-0.5 shrink-0">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    </svg>
                                                </span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-semibold text-slate-800 truncate" x-text="item.display_name.split(',')[0]"></p>
                                                    <p class="text-[10px] text-slate-500 line-clamp-1" x-text="item.display_name"></p>
                                                </div>
                                            </button>
                                        </template>
                                    </div>

                                    <template x-if="searchError">
                                        <p class="text-[11px] text-amber-600 mt-1 flex items-center space-x-1" x-text="searchError"></p>
                                    </template>
                                </div>

                                <!-- Kontainer Visual Peta Leaflet (wire:ignore agar state DOM map terjaga) -->
                                <div wire:ignore class="relative rounded-xl overflow-hidden border border-slate-200 shadow-inner bg-slate-100">
                                    <div x-ref="mapContainer" class="w-full h-56 sm:h-64 z-0 rounded-xl" style="height: 250px; min-height: 250px;"></div>
                                    
                                    <!-- Badge Petunjuk Interaksi Peta -->
                                    <div class="absolute bottom-2 left-2 right-2 pointer-events-none z-10 flex justify-center">
                                        <span class="bg-white/95 backdrop-blur-xs text-slate-600 text-[10px] font-medium px-2.5 py-1 rounded-full shadow-xs border border-slate-200/80">
                                            📍 Klik peta atau geser pin untuk menentukan titik koordinat
                                        </span>
                                    </div>
                                </div>

                                <!-- Input Latitude & Longitude (Dua Arah / Bi-directional Sync) -->
                                <div class="grid grid-cols-2 gap-3 pt-1">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                                            Latitude <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="number" 
                                            step="0.0000001" 
                                            x-model="lat"
                                            @input="onCoordInput()"
                                            wire:model.blur="lat" 
                                            required 
                                            placeholder="-6.9935" 
                                            class="w-full py-2 px-3 text-xs rounded-xl bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 font-mono transition"
                                        />
                                        @error('lat') <span class="text-[11px] text-red-600 mt-0.5 block font-medium">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                                            Longitude <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="number" 
                                            step="0.0000001" 
                                            x-model="lng"
                                            @input="onCoordInput()"
                                            wire:model.blur="lng" 
                                            required 
                                            placeholder="107.6250" 
                                            class="w-full py-2 px-3 text-xs rounded-xl bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 font-mono transition"
                                        />
                                        @error('lng') <span class="text-[11px] text-red-600 mt-0.5 block font-medium">{{ $message }}</span> @enderror
                                    </div>
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

    <!-- Modal Form Update Okupansi Pengungsi (SCRUM-56) -->
    <div 
        x-data="{ open: $wire.entangle('showOccupancyModal') }"
        x-show="open"
        x-cloak
        @keydown.escape.window="open = false; $wire.closeOccupancyModal()"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog" 
        aria-modal="true"
        aria-labelledby="occupancy-modal-title"
    >
        <!-- Backdrop Blur -->
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm cursor-pointer" 
            @click="open = false; $wire.closeOccupancyModal()"
            aria-hidden="true"
        ></div>

        <!-- Centered Wrapper -->
        <div class="min-h-full flex items-center justify-center p-4 sm:p-6 relative z-10">
            <div 
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                class="w-full max-w-lg bg-white rounded-2xl text-left shadow-2xl border border-slate-200 overflow-hidden my-6"
            >
                <form wire:submit.prevent="updateOccupancy">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900" id="occupancy-modal-title">
                                    Update Okupansi Pengungsi
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5 truncate max-w-xs sm:max-w-md">
                                    Posko: <strong class="text-slate-800">{{ $selectedShelterName }}</strong>
                                </p>
                            </div>
                            <button 
                                type="button" 
                                @click="open = false; $wire.closeOccupancyModal()" 
                                class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Info Ringkasan Kapasitas -->
                        <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200/80 mb-4 grid grid-cols-2 gap-3 text-center">
                            <div class="p-2 rounded-lg bg-white border border-slate-200/60 shadow-2xs">
                                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Kapasitas Maksimal</span>
                                <span class="text-lg font-bold text-slate-800">{{ number_format($selectedShelterCapacity) }} <span class="text-xs font-normal text-slate-400">jiwa</span></span>
                            </div>
                            <div class="p-2 rounded-lg bg-white border border-slate-200/60 shadow-2xs">
                                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Okupansi Baru</span>
                                <span class="text-lg font-bold text-blue-600">{{ number_format((int)$newOccupancy) }} <span class="text-xs font-normal text-slate-400">jiwa</span></span>
                            </div>
                        </div>

                        <!-- Form Input Okupansi Baru -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Jumlah Pengungsi Aktif (Jiwa) <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    min="0"
                                    wire:model.live="newOccupancy" 
                                    required 
                                    placeholder="Contoh: 150" 
                                    class="w-full py-2.5 px-3 text-sm font-semibold rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('newOccupancy') <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <!-- Live Dynamic Indicator Preview -->
                            @php
                                $cap = max(1, (int) $selectedShelterCapacity);
                                $occ = max(0, (int) $newOccupancy);
                                $previewPercent = round(($occ / $cap) * 100, 1);
                                $previewBadge = match (true) {
                                    $previewPercent >= 100 => ['label' => 'Penuh (100%)', 'color' => 'bg-red-50 text-red-700 border-red-200', 'bar' => 'bg-red-500'],
                                    $previewPercent >= 80 => ['label' => 'Hampir Penuh (80-99%)', 'color' => 'bg-amber-50 text-amber-700 border-amber-200', 'bar' => 'bg-amber-500'],
                                    default => ['label' => 'Tersedia (<80%)', 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'bar' => 'bg-emerald-500'],
                                };
                            @endphp

                            <div class="p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-medium text-slate-600">Simulasi Status Kapasitas:</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $previewBadge['color'] }}">
                                        {{ $previewBadge['label'] }} ({{ $previewPercent }}%)
                                    </span>
                                </div>
                                <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full {{ $previewBadge['bar'] }} rounded-full transition-all duration-300" style="width: {{ min(100, $previewPercent) }}%"></div>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-slate-500 pt-0.5">
                                    <span>Sisa kuota: <strong>{{ max(0, $cap - $occ) }}</strong> jiwa</span>
                                    @if ($occ >= $cap)
                                        <span class="text-red-600 font-semibold flex items-center">
                                            ⚠️ Posko akan otomatis diset Penuh
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end space-x-2 border-t border-slate-100 rounded-b-2xl">
                        <button 
                            type="button" 
                            @click="open = false; $wire.closeOccupancyModal()"
                            class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center space-x-1.5 px-5 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition duration-150 disabled:opacity-50 cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="updateOccupancy">Simpan Okupansi</span>
                            <span wire:loading wire:target="updateOccupancy" class="inline-flex items-center space-x-1">
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

@push('scripts')
<script>
    function shelterMapPicker() {
        return {
            lat: -6.9935,
            lng: 107.625,
            map: null,
            marker: null,
            searchQuery: '',
            isSearching: false,
            isLocating: false,
            searchResults: [],
            searchError: null,

            init() {
                const wireLat = this.$wire.get('lat');
                const wireLng = this.$wire.get('lng');
                this.lat = wireLat !== undefined && wireLat !== null && wireLat !== '' ? parseFloat(wireLat) : -6.9935;
                this.lng = wireLng !== undefined && wireLng !== null && wireLng !== '' ? parseFloat(wireLng) : 107.625;

                // Pantau perubahan properti Livewire jika diperbarui dari sisi server
                this.$watch('$wire.lat', (val) => {
                    if (val !== undefined && val !== null && val !== '') {
                        const parsed = parseFloat(val);
                        if (!isNaN(parsed) && Math.abs(parsed - this.lat) > 0.00001) {
                            this.lat = parsed;
                            this.syncMarker();
                        }
                    }
                });

                this.$watch('$wire.lng', (val) => {
                    if (val !== undefined && val !== null && val !== '') {
                        const parsed = parseFloat(val);
                        if (!isNaN(parsed) && Math.abs(parsed - this.lng) > 0.00001) {
                            this.lng = parsed;
                            this.syncMarker();
                        }
                    }
                });
            },

            setupMap() {
                this.$nextTick(() => {
                    const container = this.$refs.mapContainer;
                    if (!container) return;

                    if (typeof L === 'undefined') {
                        setTimeout(() => this.setupMap(), 200);
                        return;
                    }

                    const currentLat = parseFloat(this.lat) || -6.9935;
                    const currentLng = parseFloat(this.lng) || 107.625;

                    if (!this.map) {
                        this.map = L.map(container, {
                            center: [currentLat, currentLng],
                            zoom: 14,
                            zoomControl: true,
                            attributionControl: false
                        });

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(this.map);

                        // Pin Marker Draggable
                        this.marker = L.marker([currentLat, currentLng], {
                            draggable: true,
                            autoPan: true
                        }).addTo(this.map);

                        this.marker.bindPopup(`<b>Titik Posko Evakuasi</b><br>Lat: ${currentLat.toFixed(6)}<br>Lng: ${currentLng.toFixed(6)}`);

                        // Event drag pin marker
                        this.marker.on('dragend', (e) => {
                            const pos = e.target.getLatLng();
                            this.updateCoordinates(pos.lat, pos.lng);
                            this.marker.bindPopup(`<b>Titik Posko Evakuasi</b><br>Lat: ${pos.lat.toFixed(6)}<br>Lng: ${pos.lng.toFixed(6)}`).openPopup();
                        });

                        // Event klik pada peta
                        this.map.on('click', (e) => {
                            this.marker.setLatLng(e.latlng);
                            this.updateCoordinates(e.latlng.lat, e.latlng.lng);
                            this.marker.bindPopup(`<b>Titik Posko Evakuasi</b><br>Lat: ${e.latlng.lat.toFixed(6)}<br>Lng: ${e.latlng.lng.toFixed(6)}`).openPopup();
                        });
                    } else {
                        this.map.setView([currentLat, currentLng], this.map.getZoom() || 14);
                        if (this.marker) {
                            this.marker.setLatLng([currentLat, currentLng]);
                        }
                    }

                    // Invalidate size agar ukuran tile Leaflet pas dan tidak terdistorsi saat animasi modal selesai
                    setTimeout(() => {
                        if (this.map) {
                            this.map.invalidateSize();
                        }
                    }, 250);
                });
            },

            updateCoordinates(lat, lng) {
                const numLat = Number(parseFloat(lat).toFixed(7));
                const numLng = Number(parseFloat(lng).toFixed(7));
                this.lat = numLat;
                this.lng = numLng;
                this.$wire.set('lat', numLat);
                this.$wire.set('lng', numLng);
            },

            syncMarker() {
                const numLat = parseFloat(this.lat);
                const numLng = parseFloat(this.lng);
                if (!isNaN(numLat) && !isNaN(numLng) && this.marker && this.map) {
                    const currentPos = this.marker.getLatLng();
                    if (Math.abs(currentPos.lat - numLat) > 0.00001 || Math.abs(currentPos.lng - numLng) > 0.00001) {
                        this.marker.setLatLng([numLat, numLng]);
                        this.map.panTo([numLat, numLng]);
                    }
                }
            },

            onCoordInput() {
                const numLat = parseFloat(this.lat);
                const numLng = parseFloat(this.lng);
                if (!isNaN(numLat) && !isNaN(numLng)) {
                    this.$wire.set('lat', numLat);
                    this.$wire.set('lng', numLng);
                    this.syncMarker();
                }
            },

            searchLocation() {
                const q = (this.searchQuery || '').trim();
                if (q.length < 3) {
                    this.searchError = 'Ketik minimal 3 karakter untuk mencari nama lokasi.';
                    this.searchResults = [];
                    return;
                }

                this.isSearching = true;
                this.searchError = null;
                this.searchResults = [];

                // OpenStreetMap Nominatim Geocoding API (gratis & open-source)
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&countrycodes=id&limit=5&addressdetails=1`;

                fetch(url, {
                    headers: { 'Accept-Language': 'id,en' }
                })
                .then(res => res.json())
                .then(data => {
                    this.isSearching = false;
                    if (!data || data.length === 0) {
                        this.searchError = 'Lokasi tidak ditemukan. Coba gunakan nama tempat atau kelurahan yang lebih spesifik.';
                    } else {
                        this.searchResults = data;
                    }
                })
                .catch(err => {
                    this.isSearching = false;
                    this.searchError = 'Gagal menghubungi server pencarian lokasi.';
                    console.error(err);
                });
            },

            selectLocation(item) {
                const lat = parseFloat(item.lat);
                const lon = parseFloat(item.lon);
                if (isNaN(lat) || isNaN(lon)) return;

                this.searchResults = [];
                this.searchQuery = item.display_name.split(',')[0];
                this.searchError = null;

                if (this.map && this.marker) {
                    this.map.flyTo([lat, lon], 16, { animate: true, duration: 1.0 });
                    this.marker.setLatLng([lat, lon]);
                    this.marker.bindPopup(`<b>${item.display_name.split(',')[0]}</b><br>${item.display_name}`).openPopup();
                }

                this.updateCoordinates(lat, lon);

                // Bantu isikan alamat posko jika masih kosong
                const currentAddress = this.$wire.get('address');
                if (!currentAddress || currentAddress.trim() === '') {
                    this.$wire.set('address', item.display_name);
                }
            },

            getCurrentLocation() {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung deteksi lokasi (Geolocation).');
                    return;
                }

                this.isLocating = true;
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.isLocating = false;
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        if (this.map && this.marker) {
                            this.map.flyTo([lat, lng], 16);
                            this.marker.setLatLng([lat, lng]);
                            this.marker.bindPopup('<b>📍 Lokasi Anda Saat Ini</b>').openPopup();
                        }
                        this.updateCoordinates(lat, lng);
                    },
                    (err) => {
                        this.isLocating = false;
                        alert('Tidak dapat mendeteksi lokasi saat ini. Pastikan izin akses GPS diaktifkan.');
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            }
        };
    }
</script>
@endpush
