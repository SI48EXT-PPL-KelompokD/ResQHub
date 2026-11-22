<div class="space-y-6">

    <!-- Top Flash Notification -->
    @if ($notification)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            class="p-4 rounded-2xl flex items-center justify-between shadow-xs transition duration-200 {{ $notification['type'] === 'success' ? 'bg-emerald-50 border border-emerald-200/80 text-emerald-800' : 'bg-rose-50 border border-rose-200/80 text-rose-800' }}"
        >
            <div class="flex items-center space-x-3">
                @if ($notification['type'] === 'success')
                    <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                @else
                    <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                @endif
                <div>
                    <p class="text-sm font-semibold">{{ $notification['type'] === 'success' ? 'Berhasil!' : 'Perhatian!' }}</p>
                    <p class="text-xs text-slate-600">{{ $notification['message'] }}</p>
                </div>
            </div>
            <button wire:click="dismissNotification" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-white/60">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wider font-mono">Modul F • PBI-16</span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs text-slate-500 font-medium">Logistik & Penyaluran</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">Kelola Stok Logistik Posko</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Pantau ketersediaan barang di gudang posko, estimasi hari tersisa dari tren penyaluran, dan riwayat mutasi barang secara akurat.
            </p>
        </div>

        <div class="flex items-center space-x-3 shrink-0">
            <a 
                href="{{ route('admin.donation-receipts.index') }}"
                wire:navigate
                class="inline-flex items-center space-x-2 px-3.5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs sm:text-sm font-semibold transition duration-150"
            >
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <span>Penerimaan Donasi</span>
            </a>
            <button 
                wire:click="openIncomingModal"
                class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-xs shadow-blue-500/20 transition duration-150 active:scale-95 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Catat Barang Masuk</span>
            </button>
        </div>
    </div>

    <!-- Summary Statistics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Jenis Barang -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Jenis Barang</span>
                <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ $totalItems }}</span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">Katalog aktif di gudang</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>

        <!-- Total Kuantitas Barang -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Kuantitas Fisik</span>
                <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ number_format($totalQty, 0, ',', '.') }}</span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">Akumulasi seluruh unit</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Stok Menipis Alert -->
        <div class="bg-white p-5 rounded-2xl border {{ $totalDepleted > 0 ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200/80' }} shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold {{ $totalDepleted > 0 ? 'text-amber-700' : 'text-slate-500' }} uppercase tracking-wider block">Stok Menipis</span>
                <span class="text-2xl font-bold {{ $totalDepleted > 0 ? 'text-amber-700' : 'text-slate-900' }} mt-1 block">{{ $totalDepleted }}</span>
                <span class="text-[11px] text-amber-600/80 mt-0.5 block">Di bawah ambang batas</span>
            </div>
            <div class="w-12 h-12 rounded-xl {{ $totalDepleted > 0 ? 'bg-amber-100 text-amber-600 animate-pulse' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        <!-- Stok Habis -->
        <div class="bg-white p-5 rounded-2xl border {{ $totalEmpty > 0 ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200/80' }} shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold {{ $totalEmpty > 0 ? 'text-rose-700' : 'text-slate-500' }} uppercase tracking-wider block">Stok Habis (0)</span>
                <span class="text-2xl font-bold {{ $totalEmpty > 0 ? 'text-rose-700' : 'text-slate-900' }} mt-1 block">{{ $totalEmpty }}</span>
                <span class="text-[11px] text-rose-500 mt-0.5 block">Dapat dihapus jika tidak ada kebutuhan</span>
            </div>
            <div class="w-12 h-12 rounded-xl {{ $totalEmpty > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
        </div>

    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        
        <!-- Search Input -->
        <div class="relative flex-1 min-w-[240px]">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama barang, kategori, satuan..." 
                class="w-full pl-10 pr-4 py-2 bg-slate-50 hover:bg-white focus:bg-white text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
            />
        </div>

        <!-- Filter Selectors -->
        <div class="flex flex-wrap items-center gap-2">
            
            <!-- Posko Selector -->
            @if ($this->availableShelters->count() > 1 || Auth::user()->isAdmin())
                <select 
                    wire:model.live="shelterFilter"
                    class="px-3 py-2 bg-slate-50 hover:bg-white focus:bg-white text-xs text-slate-700 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition font-medium"
                >
                    @if (Auth::user()->isAdmin())
                        <option value="">Semua Posko</option>
                    @endif
                    @foreach ($this->availableShelters as $sh)
                        <option value="{{ $sh->id }}">{{ $sh->name }}</option>
                    @endforeach
                </select>
            @endif

            <!-- Kategori Selector -->
            <select 
                wire:model.live="categoryFilter"
                class="px-3 py-2 bg-slate-50 hover:bg-white focus:bg-white text-xs text-slate-700 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition font-medium"
            >
                <option value="">Semua Kategori</option>
                <option value="pangan">Pangan</option>
                <option value="sandang">Sandang</option>
                <option value="kebersihan">Kebersihan</option>
                <option value="obat_obatan">Obat-obatan</option>
                <option value="perlengkapan_bayi">Perlengkapan Bayi</option>
                <option value="peralatan">Peralatan</option>
            </select>

            <!-- Status Selector -->
            <select 
                wire:model.live="statusFilter"
                class="px-3 py-2 bg-slate-50 hover:bg-white focus:bg-white text-xs text-slate-700 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition font-medium"
            >
                <option value="">Semua Status Stok</option>
                <option value="menipis">⚠️ Menipis (&lt; Ambang)</option>
                <option value="aman">✅ Aman (&ge; Ambang)</option>
                <option value="habis">⛔ Habis (0)</option>
            </select>

            @if ($search || $categoryFilter || $statusFilter || ($shelterFilter && Auth::user()->isAdmin()))
                <button 
                    wire:click="$set('search', ''); $set('categoryFilter', ''); $set('statusFilter', ''); $set('shelterFilter', '');"
                    class="px-2.5 py-2 text-xs text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-xl font-medium transition cursor-pointer"
                    title="Reset Filter"
                >
                    Reset Filter
                </button>
            @endif

        </div>

    </div>

    <!-- Main Stock Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4 sm:px-6">Barang & Kategori</th>
                        <th class="py-3.5 px-4">Posko</th>
                        <th class="py-3.5 px-4 text-right">Jumlah Stok</th>
                        <th class="py-3.5 px-4 text-center">Ambang Menipis</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4">Estimasi Hari Tersisa</th>
                        <th class="py-3.5 px-4 sm:px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse ($stocks as $stock)
                        @php
                            $estimate = $stock->days_remaining_estimate;
                            $isDepleted = $stock->isDepleted();
                            $isEmpty = $stock->quantity <= 0;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition duration-150 {{ $isEmpty ? 'bg-rose-50/20' : ($isDepleted ? 'bg-amber-50/20' : '') }}">
                            
                            <!-- Barang & Kategori -->
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 {{ $isEmpty ? 'bg-rose-100 text-rose-700' : ($isDepleted ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                        {{ substr($stock->item->name ?? 'B', 0, 2) }}
                                    </div>
                                    <div>
                                        <span class="font-semibold text-slate-900 block leading-tight">{{ $stock->item->name }}</span>
                                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                            {{ $stock->item->category_label }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Posko -->
                            <td class="py-4 px-4">
                                <span class="font-medium text-slate-700 block">{{ $stock->shelter->name }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $stock->shelter->disasterEvent->name ?? 'Bencana' }}</span>
                            </td>

                            <!-- Jumlah Stok & Satuan -->
                            <td class="py-4 px-4 text-right">
                                <span class="text-base font-bold text-slate-900 {{ $isEmpty ? 'text-rose-600' : ($isDepleted ? 'text-amber-600' : 'text-slate-900') }}">
                                    {{ number_format($stock->quantity, 0, ',', '.') }}
                                </span>
                                <span class="text-xs text-slate-500 font-medium ml-1">{{ $stock->item->unit }}</span>
                            </td>

                            <!-- Ambang Menipis -->
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-mono font-medium">
                                    <span>&lt; {{ $stock->min_threshold }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $stock->item->unit }}</span>
                                </span>
                            </td>

                            <!-- Status Badge -->
                            <td class="py-4 px-4 text-center">
                                @if ($isEmpty)
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Habis</span>
                                    </span>
                                @elseif ($isDepleted)
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Menipis</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Aman</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Estimasi Hari Tersisa (Rata-rata Penyaluran 3 Hari) -->
                            <td class="py-4 px-4">
                                <div class="space-y-0.5">
                                    <div class="flex items-center space-x-2">
                                        @if ($isEmpty)
                                            <span class="text-xs font-bold text-rose-600">0 hari (Habis)</span>
                                        @elseif ($estimate['days'] !== null)
                                            <span class="text-xs font-bold {{ $estimate['is_low'] ? 'text-amber-600' : 'text-slate-800' }}">
                                                {{ $estimate['label'] }}
                                            </span>
                                            @if ($estimate['is_low'])
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Kritis</span>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-400 font-medium">∞ (Tidak ada tren)</span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-slate-400 block">
                                        Penyaluran 3 hr: {{ $estimate['total_3d'] }} {{ $stock->item->unit }} (avg {{ $estimate['avg_daily'] }}/hr)
                                    </span>
                                </div>
                            </td>

                            <!-- Aksi Menu -->
                            <td class="py-4 px-4 sm:px-6 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    
                                    <!-- Tombol Ajukan Kebutuhan (PBI-11) -->
                                    <button 
                                        wire:click="openNeedModal({{ $stock->id }})"
                                        class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 hover:text-blue-800 rounded-lg text-xs font-semibold transition inline-flex items-center space-x-1 cursor-pointer"
                                        title="Ajukan Kebutuhan Bantuan (PBI-11)"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                        </svg>
                                        <span class="hidden sm:inline">Ajukan Kebutuhan</span>
                                    </button>

                                    <!-- Tombol Riwayat Mutasi -->
                                    <button 
                                        wire:click="openHistoryModal({{ $stock->id }})"
                                        class="p-1.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition cursor-pointer"
                                        title="Lihat Riwayat Mutasi"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>

                                    <!-- Tombol Ubah (Penyesuaian Stok) -->
                                    <button 
                                        wire:click="openAdjustModal({{ $stock->id }})"
                                        class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer"
                                        title="Sesuaikan Stok (Hitung fisik / rusak / kedaluwarsa)"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <!-- Tombol Hapus -->
                                    <button 
                                        wire:click="openDeleteModal({{ $stock->id }})"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer"
                                        title="Hapus Stok Logistik"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700">Tidak ada data stok logistik</p>
                                    <p class="text-xs text-slate-400 mt-1">Belum ada barang yang tercatat atau tidak ada data yang cocok dengan filter pencarian.</p>
                                    <button 
                                        wire:click="openIncomingModal"
                                        class="mt-4 inline-flex items-center space-x-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>Catat Stok Pertama</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Pagination -->
        @if ($stocks->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $stocks->links() }}
            </div>
        @endif

    </div>

    <!-- ================================================================= -->
    <!-- MODAL 1: TAMBAH STOK AWAL / BARANG MASUK DI LUAR DONASI          -->
    <!-- ================================================================= -->
    @if ($showIncomingModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeIncomingModal"></div>

            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 my-8">
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Catat Barang Masuk</h3>
                            <p class="text-xs text-slate-500">Stok awal atau barang masuk di luar donasi (stok opname, bantuan pemerintah)</p>
                        </div>
                    </div>
                    <button wire:click="closeIncomingModal" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveIncomingStock" class="mt-5 space-y-4">
                    
                    <!-- Pilih Posko -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Posko Tujuan <span class="text-rose-500">*</span></label>
                        <select 
                            wire:model="incomingShelterId"
                            class="w-full px-3.5 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition"
                        >
                            @foreach ($this->availableShelters as $sh)
                                <option value="{{ $sh->id }}">{{ $sh->name }} (Kapasitas: {{ $sh->capacity }})</option>
                            @endforeach
                        </select>
                        @error('incomingShelterId') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Pilih Barang dari Katalog -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Barang dari Katalog <span class="text-rose-500">*</span></label>
                        <select 
                            wire:model.live="incomingItemId"
                            class="w-full px-3.5 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition font-medium"
                        >
                            <option value="">-- Pilih Barang dari Katalog --</option>
                            @foreach ($this->availableItems as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->name }} ({{ $item->category_label }} • Satuan: {{ $item->unit }})
                                </option>
                            @endforeach
                        </select>
                        @error('incomingItemId') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Jumlah Masuk -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jumlah Barang <span class="text-rose-500">*</span></label>
                            <input 
                                type="number" 
                                min="1"
                                wire:model="incomingQuantity"
                                placeholder="Contoh: 100" 
                                class="w-full px-3.5 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition font-semibold"
                            />
                            @error('incomingQuantity') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Ambang Menipis Posko -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ambang Menipis <span class="text-rose-500">*</span></label>
                            <input 
                                type="number" 
                                min="0"
                                wire:model="incomingMinThreshold"
                                placeholder="Contoh: 20" 
                                class="w-full px-3.5 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition font-semibold"
                            />
                            @error('incomingMinThreshold') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                            <span class="text-[10px] text-slate-400 mt-1 block">Batas peringatan status "Menipis"</span>
                        </div>
                    </div>

                    <!-- Sumber Barang -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Sumber Barang <span class="text-rose-500">*</span></label>
                        <select 
                            wire:model="incomingSource"
                            class="w-full px-3.5 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition"
                        >
                            <option value="Stok Opname">Stok Opname / Saldo Awal</option>
                            <option value="Bantuan Pemerintah">Bantuan Pemerintah (BPBD/Kemensos/Pemda)</option>
                            <option value="Pengadaan Mandiri Posko">Pengadaan Mandiri Posko</option>
                            <option value="Hibah Lembaga/CSR">Hibah Lembaga / Mitra CSR</option>
                            <option value="Lainnya">Lainnya (Tulis pada Catatan)</option>
                        </select>
                        @error('incomingSource') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Catatan / Keterangan Sumber -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Tambahan (Opsional)</label>
                        <input 
                            type="text" 
                            wire:model="incomingNotes"
                            placeholder="Contoh: Bantuan logistik drop dari gudang BPBD Prov" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition"
                        />
                        @error('incomingNotes') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="p-3 bg-blue-50/70 border border-blue-200/80 rounded-xl text-xs text-blue-800 flex items-start space-x-2.5">
                        <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Pencatatan ini akan langsung menambahkan stok posko dan secara otomatis <strong>tercatat sebagai riwayat mutasi masuk</strong>.</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button 
                            type="button" 
                            wire:click="closeIncomingModal"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs sm:text-sm font-semibold hover:bg-slate-50 transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-xs shadow-blue-500/20 transition active:scale-95 cursor-pointer"
                        >
                            Simpan & Catat Mutasi
                        </button>
                    </div>

                </form>

            </div>
        </div>
    @endif

    <!-- ================================================================= -->
    <!-- MODAL 2: PENYESUAIAN STOK (UBAH / KOREKSI / RUSAK / KEDALUWARSA)   -->
    <!-- ================================================================= -->
    @if ($showAdjustModal && $selectedStock)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeAdjustModal"></div>

            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 my-8">
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Penyesuaian Stok</h3>
                            <p class="text-xs text-slate-500">Koreksi hasil hitung fisik, barang rusak, atau kedaluwarsa</p>
                        </div>
                    </div>
                    <button wire:click="closeAdjustModal" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Info Barang Saat Ini -->
                <div class="mt-4 p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">{{ $selectedStock->item->name }}</span>
                        <span class="text-[11px] text-slate-500">{{ $selectedStock->shelter->name }} • {{ $selectedStock->item->category_label }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Stok Sistem Saat Ini</span>
                        <span class="text-base font-extrabold text-blue-600">{{ $selectedStock->quantity }} {{ $selectedStock->item->unit }}</span>
                    </div>
                </div>

                <form wire:submit.prevent="saveAdjustment" class="mt-4 space-y-4">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Jumlah Fisik Baru -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jumlah Stok Fisik Baru <span class="text-rose-500">*</span></label>
                            <input 
                                type="number" 
                                min="0"
                                wire:model.live="adjustNewQuantity"
                                class="w-full px-3.5 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition font-extrabold text-base"
                            />
                            @error('adjustNewQuantity') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Ambang Menipis -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ambang Menipis <span class="text-rose-500">*</span></label>
                            <input 
                                type="number" 
                                min="0"
                                wire:model="adjustMinThreshold"
                                class="w-full px-3.5 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition font-semibold"
                            />
                            @error('adjustMinThreshold') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Perhitungan Selisih Mutasi -->
                    @php
                        $calcDiff = is_numeric($adjustNewQuantity) ? ((int) $adjustNewQuantity - $selectedStock->quantity) : 0;
                    @endphp
                    <div class="p-3 rounded-xl border text-xs flex items-center justify-between {{ $calcDiff < 0 ? 'bg-rose-50 border-rose-200 text-rose-800' : ($calcDiff > 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-slate-50 border-slate-200 text-slate-600') }}">
                        <span class="font-medium">Selisih mutasi penyesuaian:</span>
                        <span class="font-bold text-sm">
                            {{ ($calcDiff > 0 ? '+' : '') . $calcDiff }} {{ $selectedStock->item->unit }}
                        </span>
                    </div>

                    <!-- Alasan Penyesuaian (Wajib) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Alasan Penyesuaian <span class="text-rose-500">* (Wajib Diisi)</span>
                        </label>
                        <textarea 
                            wire:model="adjustReason"
                            rows="2"
                            placeholder="Contoh: Koreksi hasil hitung fisik stok opname bulanan, atau 5 kg rusak lembab basah..."
                            class="w-full px-3.5 py-2 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition"
                        ></textarea>
                        @error('adjustReason') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <button type="button" wire:click="$set('adjustReason', 'Koreksi hasil hitung fisik (stok opname)')" class="text-[11px] px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 cursor-pointer">
                                Hitung Fisik
                            </button>
                            <button type="button" wire:click="$set('adjustReason', 'Barang rusak / kemasan bocor saat penyimpanan')" class="text-[11px] px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 cursor-pointer">
                                Barang Rusak
                            </button>
                            <button type="button" wire:click="$set('adjustReason', 'Barang telah melewati masa kedaluwarsa')" class="text-[11px] px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 cursor-pointer">
                                Kedaluwarsa
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button 
                            type="button" 
                            wire:click="closeAdjustModal"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs sm:text-sm font-semibold hover:bg-slate-50 transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit"
                            class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs sm:text-sm font-semibold shadow-xs shadow-amber-500/20 transition active:scale-95 cursor-pointer"
                        >
                            Simpan Penyesuaian
                        </button>
                    </div>

                </form>

            </div>
        </div>
    @endif

    <!-- ================================================================= -->
    <!-- MODAL 3: RIWAYAT MUTASI STOK                                     -->
    <!-- ================================================================= -->
    @if ($showHistoryModal && $historyStock)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeHistoryModal"></div>

            <div class="relative bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 my-8">
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Riwayat Mutasi Barang</h3>
                            <p class="text-xs text-slate-500">{{ $historyStock->item->name }} • {{ $historyStock->shelter->name }}</p>
                        </div>
                    </div>
                    <button wire:click="closeHistoryModal" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Ringkasan Stok Terkini -->
                <div class="mt-4 p-3 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-slate-500 font-medium">Stok Saat Ini:</span>
                        <span class="font-extrabold text-slate-900 ml-1">{{ $historyStock->quantity }} {{ $historyStock->item->unit }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium">Ambang Menipis:</span>
                        <span class="font-bold text-amber-600 ml-1">{{ $historyStock->min_threshold }} {{ $historyStock->item->unit }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium">Total Mutasi:</span>
                        <span class="font-bold text-indigo-600 ml-1">{{ $historyStock->movements->count() }} kali</span>
                    </div>
                </div>

                <!-- List Riwayat Mutasi -->
                <div class="mt-4 max-h-96 overflow-y-auto divide-y divide-slate-100">
                    @forelse ($historyStock->movements as $m)
                        <div class="py-3 flex items-start justify-between space-x-3">
                            <div class="flex items-start space-x-3">
                                
                                <!-- Icon Tipe Mutasi -->
                                @if ($m->type === 'masuk')
                                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                        </svg>
                                    </div>
                                @elseif ($m->type === 'keluar')
                                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </div>
                                @endif

                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-bold uppercase tracking-wider {{ $m->type === 'masuk' ? 'text-emerald-700' : ($m->type === 'keluar' ? 'text-rose-700' : 'text-amber-700') }}">
                                            {{ $m->type_label }}
                                        </span>
                                        <span class="text-[10px] text-slate-400">•</span>
                                        <span class="text-[11px] text-slate-500 font-mono">
                                            {{ $m->created_at->format('d M Y, H:i') }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-700 font-medium mt-0.5">{{ $m->reason ?? 'Tanpa alasan tercatat' }}</p>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">
                                        Dicatat oleh: <strong class="text-slate-600">{{ $m->creator->name ?? 'Sistem' }}</strong>
                                    </span>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="text-sm font-extrabold {{ $m->type === 'masuk' ? 'text-emerald-600' : ($m->type === 'keluar' ? 'text-rose-600' : ($m->quantity >= 0 ? 'text-emerald-600' : 'text-rose-600')) }}">
                                    {{ $m->formatted_quantity }} {{ $historyStock->item->unit }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">
                            Belum ada riwayat mutasi untuk barang ini.
                        </div>
                    @endforelse
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-100 mt-4">
                    <button 
                        type="button" 
                        wire:click="closeHistoryModal"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    @endif

    <!-- ================================================================= -->
    <!-- MODAL 4: HAPUS STOK LOGISTIK                                     -->
    <!-- ================================================================= -->
    @if ($showDeleteModal && $deleteStock)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeDeleteModal"></div>

            <div class="relative bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 my-8">
                
                <div class="text-center">
                    <div class="w-14 h-14 rounded-3xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900">Hapus Data Stok Logistik?</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Anda akan menghapus baris stok <strong>{{ $deleteStock->item->name }}</strong> di posko <strong>{{ $deleteStock->shelter->name }}</strong>.
                    </p>
                </div>

                @if ($deleteBlockReason)
                    <div class="mt-4 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1">
                        <div class="flex items-center space-x-1.5 font-bold">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Stok Tidak Dapat Dihapus</span>
                        </div>
                        <p class="text-slate-600">{{ $deleteBlockReason }}</p>
                    </div>
                @else
                    <div class="mt-4 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800">
                        <div class="flex items-center space-x-1.5 font-bold mb-1">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Syarat Terpenuhi</span>
                        </div>
                        <p class="text-slate-600">Jumlah stok saat ini adalah <strong>0</strong> dan tidak ada kebutuhan terbuka. Riwayat mutasi historis akan tetap tersimpan aman di sistem.</p>
                    </div>
                @endif

                <div class="flex items-center justify-end space-x-3 pt-5 border-t border-slate-100 mt-5">
                    <button 
                        type="button" 
                        wire:click="closeDeleteModal"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition cursor-pointer"
                    >
                        Tutup
                    </button>
                    @if (! $deleteBlockReason)
                        <button 
                            type="button" 
                            wire:click="deleteStockAction"
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs shadow-rose-500/20 transition active:scale-95 cursor-pointer"
                        >
                            Hapus Baris Stok
                        </button>
                    @endif
                </div>

            </div>
        </div>
    @endif

    <!-- ================================================================= -->
    <!-- MODAL 5: AJUKAN KEBUTUHAN BANTUAN POSKO (PBI-11)                  -->
    <!-- ================================================================= -->
    @if ($showNeedModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeNeedModal"></div>

            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 my-8">
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider font-mono">Form PBI-11</span>
                            <h3 class="text-lg font-bold text-slate-900">Ajukan Kebutuhan Bantuan Posko</h3>
                        </div>
                    </div>
                    <button wire:click="closeNeedModal" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="submitAidNeed" class="mt-4 space-y-4">
                    
                    <!-- Info Barang & Posko Terisi -->
                    <div class="p-3.5 bg-blue-50/70 border border-blue-200/80 rounded-2xl flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase text-blue-600 tracking-wider">Barang Bantuan (Terisi Otomatis)</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-0.5">{{ $needItem->name ?? 'Barang' }}</h4>
                            <span class="text-xs text-slate-500">{{ $needShelter->name ?? 'Posko' }} • {{ $needItem->category_label ?? '' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold px-2 py-0.5 bg-white text-blue-700 rounded-md border border-blue-200">
                                Satuan: {{ $needItem->unit ?? 'Unit' }}
                            </span>
                        </div>
                    </div>

                    <!-- Jumlah yang Dibutuhkan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Jumlah Dibutuhkan <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            min="1"
                            wire:model="needQty"
                            class="w-full px-3.5 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition font-bold"
                        />
                        @error('needQty') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Tingkat Prioritas -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tingkat Prioritas <span class="text-rose-500">*</span></label>
                            <select 
                                wire:model="needPriority"
                                class="w-full px-3.5 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition font-medium"
                            >
                                <option value="mendesak">🚨 Mendesak (Stok Habis / Kritis)</option>
                                <option value="tinggi">⚡ Tinggi (Stok Menipis)</option>
                                <option value="normal">📦 Normal (Kebutuhan Rutin)</option>
                            </select>
                            @error('needPriority') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tenggat Waktu (Deadline) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tenggat Waktu</label>
                            <input 
                                type="date" 
                                wire:model="needDeadline"
                                class="w-full px-3.5 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition text-xs font-mono"
                            />
                            @error('needDeadline') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button 
                            type="button" 
                            wire:click="closeNeedModal"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs sm:text-sm font-semibold hover:bg-slate-50 transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-xs shadow-blue-500/20 transition active:scale-95 cursor-pointer"
                        >
                            Kirim Pengajuan Kebutuhan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    @endif

</div>

