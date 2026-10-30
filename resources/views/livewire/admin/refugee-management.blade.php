<div class="space-y-6">

    <!-- Toast Notification & Warnings -->
    @if ($notification)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false; $wire.dismissNotification(); }, 6000)"
            role="alert"
            class="fixed top-20 right-6 z-50 max-w-md w-full p-4 rounded-xl shadow-lg border transition-all transform ease-out duration-300 {{ $notification['type'] === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800' }}"
        >
            <div class="flex items-start justify-between space-x-3">
                <div class="flex items-start space-x-2.5">
                    @if ($notification['type'] === 'error')
                        <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    @endif
                    <div class="space-y-1">
                        <p class="text-xs sm:text-sm font-semibold">{{ $notification['message'] }}</p>
                        @if (!empty($notification['warnings']))
                            <ul class="mt-1.5 space-y-1 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-lg p-2">
                                @foreach ($notification['warnings'] as $warning)
                                    <li class="flex items-start space-x-1.5">
                                        <span class="font-bold text-amber-600">•</span>
                                        <span>{{ $warning }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                <button wire:click="dismissNotification" class="text-slate-400 hover:text-slate-600 p-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Breadcrumb -->
    <div class="text-xs font-medium text-slate-400">
        <span>Beranda</span>
        <span class="mx-1.5">/</span>
        <span class="text-slate-600">Pengungsi</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                Data Pengungsi · {{ $currentShelter ? str_replace(['Posko Utama ', 'Posko Evakuasi ', 'Posko Darurat '], '', $currentShelter->name) : 'Semua Posko' }}
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">
                PBI-07 · Check-in, ubah data, pulang, dan pindah posko per kepala keluarga
            </p>
        </div>

        <div class="flex items-center space-x-3">
            @if ($accessibleShelters->count() > 1)
                <select 
                    wire:model.live="shelterFilter"
                    class="py-2.5 px-3.5 text-xs sm:text-sm bg-white text-slate-700 font-medium rounded-xl border border-slate-200 shadow-2xs focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                >
                    <option value="">Semua Posko Kelolaan</option>
                    @foreach ($accessibleShelters as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            @endif

            @if ($canManage)
                <button 
                    type="button"
                    wire:click="openCreateModal"
                    class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition duration-150 cursor-pointer"
                >
                    <span>Check-in Pengungsi</span>
                </button>
            @endif
        </div>
    </div>

    <!-- 3 Key Summary Metric Cards (Sesuai Mockup PBI-07) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <!-- Card 1: Pengungsi aktif -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Pengungsi aktif</span>
            <div class="mt-1.5 flex items-baseline space-x-1.5">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $statistics['active_members'] }} jiwa
                </span>
            </div>
            <p class="mt-1.5 text-xs text-slate-400">
                {{ $statistics['active_families'] }} kepala keluarga
            </p>
        </div>

        <!-- Card 2: Kelompok rentan -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Kelompok rentan</span>
            <div class="mt-1.5 flex items-baseline space-x-1.5">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $statistics['total_vulnerable'] }}
                </span>
            </div>
            <p class="mt-1.5 text-xs text-slate-400">
                {{ $statistics['total_infants'] }} balita, {{ $statistics['total_elderly'] }} lansia, {{ $statistics['total_pregnant'] }} ibu hamil, {{ $statistics['total_disabled'] }} disabilitas
            </p>
        </div>

        <!-- Card 3: Sisa kapasitas -->
        @php
            $remainingCapacity = max(0, $statistics['total_capacity'] - $statistics['total_occupancy']);
            $isOverCapacity = $statistics['total_occupancy'] > $statistics['total_capacity'] && $statistics['total_capacity'] > 0;
        @endphp
        <div class="bg-white rounded-2xl p-5 border {{ $isOverCapacity ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200/80' }} shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 block">Sisa kapasitas</span>
                @if ($isOverCapacity)
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Melebihi Kapasitas</span>
                @endif
            </div>
            <div class="mt-1.5 flex items-baseline space-x-1.5">
                <span class="text-2xl sm:text-3xl font-extrabold {{ $isOverCapacity ? 'text-amber-700' : 'text-slate-900' }} tracking-tight">
                    {{ $remainingCapacity }}
                </span>
            </div>
            <p class="mt-1.5 text-xs text-slate-400">
                dari {{ $statistics['total_capacity'] }} jiwa
            </p>
        </div>

    </div>

    <!-- Main Content Split Layout: Table & Filters (Left) + Dynamic Action Panel (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Filter Controls & Refugees Table -->
        <div class="lg:col-span-7 xl:col-span-7 space-y-4">

            <!-- Filter Bar (Status, Urutkan, & Pencarian) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                
                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                    <select 
                        wire:model.live="statusFilter"
                        class="w-full py-2.5 px-3.5 text-xs sm:text-sm bg-white text-slate-700 rounded-xl border border-slate-200 shadow-2xs focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                    >
                        <option value="">Semua Status</option>
                        <option value="di_posko">Di Posko</option>
                        <option value="pindah">Pindah</option>
                        <option value="pulang">Pulang</option>
                    </select>
                </div>

                <!-- Sort Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Urutkan</label>
                    <select 
                        wire:model.live="sortFilter"
                        class="w-full py-2.5 px-3.5 text-xs sm:text-sm bg-white text-slate-700 rounded-xl border border-slate-200 shadow-2xs focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                    >
                        <option value="latest">Check-in terbaru</option>
                        <option value="oldest">Check-in terlama</option>
                        <option value="members_desc">Jumlah jiwa terbanyak</option>
                        <option value="name_asc">Nama kepala keluarga (A-Z)</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pencarian</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="Cari KK atau NIK..." 
                            class="w-full pl-9 pr-3 py-2.5 text-xs sm:text-sm bg-white text-slate-800 rounded-xl border border-slate-200 shadow-2xs focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                        />
                    </div>
                </div>

            </div>

            <!-- Refugees List Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50/90 text-slate-500 text-[11px] font-semibold uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3.5 px-5">Kepala Keluarga</th>
                                <th class="py-3.5 px-4">NIK</th>
                                <th class="py-3.5 px-3 text-center">Jiwa</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($refugees as $refugee)
                                @php
                                    $isSelected = $refugeeId === $refugee->id;
                                    $statusBadge = match ($refugee->status) {
                                        'di_posko' => ['label' => 'Di Posko', 'classes' => 'bg-emerald-100/90 text-emerald-800'],
                                        'pindah'   => ['label' => 'Pindah', 'classes' => 'bg-blue-100/90 text-blue-700'],
                                        'pulang'   => ['label' => 'Pulang', 'classes' => 'bg-slate-100 text-slate-600'],
                                        default    => ['label' => ucfirst($refugee->status), 'classes' => 'bg-slate-100 text-slate-600'],
                                    };
                                @endphp
                                <tr 
                                    wire:key="refugee-{{ $refugee->id }}"
                                    class="transition {{ $isSelected ? 'bg-blue-50/75' : 'hover:bg-slate-50/60' }}"
                                >
                                    <!-- Kepala Keluarga -->
                                    <td class="py-3.5 px-5">
                                        <button 
                                            type="button"
                                            wire:click="openDetailModal({{ $refugee->id }})"
                                            class="text-left group focus:outline-none"
                                        >
                                            <span class="font-semibold text-slate-900 group-hover:text-blue-600 transition block leading-snug">
                                                {{ $refugee->head_name }}
                                            </span>
                                            @if ($accessibleShelters->count() > 1 && $refugee->shelter)
                                                <span class="text-[11px] text-slate-400 block mt-0.5">
                                                    {{ $refugee->shelter->name }}
                                                </span>
                                            @endif
                                        </button>
                                    </td>

                                    <!-- NIK Tersamar -->
                                    <td class="py-3.5 px-4 font-mono text-xs text-slate-600">
                                        {{ $refugee->masked_nik }}
                                    </td>

                                    <!-- Jumlah Jiwa -->
                                    <td class="py-3.5 px-3 text-center font-medium text-slate-800">
                                        {{ $refugee->members }}
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusBadge['classes'] }}">
                                            {{ $statusBadge['label'] }}
                                        </span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3.5 px-5 text-right">
                                        <div class="inline-flex items-center justify-end space-x-2">
                                            @if ($canManage && $refugee->status === 'di_posko')
                                                <button 
                                                    type="button"
                                                    wire:click="openEditModal({{ $refugee->id }})"
                                                    class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg border border-slate-200 shadow-2xs transition cursor-pointer"
                                                >
                                                    Ubah
                                                </button>
                                                <button 
                                                    type="button"
                                                    wire:click="openDeleteModal({{ $refugee->id }})"
                                                    class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition cursor-pointer"
                                                >
                                                    Hapus
                                                </button>
                                            @else
                                                <button 
                                                    type="button"
                                                    wire:click="openDetailModal({{ $refugee->id }})"
                                                    class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg border border-slate-200 shadow-2xs transition cursor-pointer"
                                                >
                                                    Detail
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400">
                                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <p class="text-sm font-semibold text-slate-700">Belum ada data pengungsi ditemukan</p>
                                        <p class="text-xs text-slate-400 mt-1">Sesuaikan filter status atau lakukan check-in pengungsi baru.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Privacy Footnote (Sesuai Mockup) -->
                <div class="px-5 py-3.5 border-t border-slate-100 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <p class="text-xs text-slate-400">
                        NIK ditampilkan tersamar sesuai UU 27/2022 tentang Pelindungan Data Pribadi
                    </p>
                </div>

                @if ($refugees->hasPages())
                    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                        {{ $refugees->links() }}
                    </div>
                @endif
            </div>

        </div>

        <!-- Right Column: Contextual Side Panel (Edit / Check-In / Detail) -->
        <div class="lg:col-span-5 xl:col-span-5">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6">

                {{-- MODE 1: UBAH DATA & PERUBAHAN STATUS (Sesuai Mockup Panel Kanan) --}}
                @if ($panelMode === 'edit' && $selectedRefugee)
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-slate-900">
                            Ubah data: {{ $selectedRefugee->head_name }}
                        </h2>
                        <button 
                            type="button"
                            wire:click="openDetailModal({{ $selectedRefugee->id }})"
                            class="text-xs font-semibold text-blue-600 hover:text-blue-700"
                        >
                            Lihat Riwayat
                        </button>
                    </div>

                    @if (!empty($formWarnings))
                        <div class="mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs space-y-1">
                            @foreach ($formWarnings as $w)
                                <p class="font-medium">{{ $w['message'] }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form wire:submit.prevent="updateRefugee" class="space-y-4">
                        
                        <!-- Row 1: Nama Kepala Keluarga & NIK -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Nama kepala keluarga</label>
                                <input 
                                    type="text" 
                                    wire:model="head_name" 
                                    required
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('head_name') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">NIK (opsional)</label>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.400ms="nik" 
                                    maxlength="16"
                                    placeholder="{{ $selectedRefugee->masked_nik }}"
                                    class="w-full py-2 px-3 text-xs sm:text-sm font-mono bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('nik') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Row 2: Jumlah Anggota & Balita -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Jumlah anggota</label>
                                <input 
                                    type="number" 
                                    min="1"
                                    wire:model.live="members" 
                                    required
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('members') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Balita</label>
                                <input 
                                    type="number" 
                                    min="0"
                                    wire:model="infants" 
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('infants') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Row 3: Lansia & Ibu Hamil/Menyusui -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Lansia</label>
                                <input 
                                    type="number" 
                                    min="0"
                                    wire:model="elderly" 
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('elderly') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Ibu hamil/menyusui</label>
                                <input 
                                    type="number" 
                                    min="0"
                                    wire:model="pregnant" 
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('pregnant') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Row 4: Penyandang Disabilitas -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Penyandang disabilitas</label>
                            <input 
                                type="number" 
                                min="0"
                                wire:model="disabled" 
                                class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            />
                            @error('disabled') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Row 5: Perubahan Status -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Perubahan status</label>
                            <select 
                                wire:model.live="statusAction"
                                class="w-full py-2.5 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            >
                                <option value="tetap">Tetap di posko</option>
                                <option value="pindah">Pindah posko</option>
                                <option value="pulang">Pulang</option>
                            </select>
                        </div>

                        <!-- Row 6: Posko Tujuan (Aktif bila Pindah Posko) -->
                        @if ($statusAction === 'pindah')
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Posko tujuan</label>
                                <select 
                                    wire:model="to_shelter_id"
                                    class="w-full py-2.5 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                >
                                    <option value="">-- Pilih posko tujuan --</option>
                                    @foreach ($destinationShelters as $dest)
                                        <option value="{{ $dest->id }}">
                                            {{ str_replace(['Posko Utama ', 'Posko Evakuasi ', 'Posko Darurat '], '', $dest->name) }} (sisa {{ $dest->availableCapacity() }} jiwa)
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_shelter_id') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Okupansi posko asal dan tujuan diperbarui otomatis, riwayat masuk/pindah/pulang tersimpan.
                        </p>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <button 
                                type="button" 
                                wire:click="closeEditModal"
                                class="w-full py-2.5 px-4 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 transition cursor-pointer"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition cursor-pointer"
                            >
                                Simpan Perubahan
                            </button>
                        </div>

                    </form>

                {{-- MODE 2: DETAIL PENGUNGSI & RIWAYAT MASUK / PINDAH / PULANG --}}
                @elseif ($panelMode === 'detail' && $selectedRefugee)
                    <div class="space-y-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">
                                    Detail Keluarga: {{ $selectedRefugee->head_name }}
                                </h2>
                                <p class="text-xs font-mono text-slate-500 mt-0.5">
                                    NIK: {{ $selectedRefugee->masked_nik }}
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $selectedRefugee->status === 'di_posko' ? 'bg-emerald-100 text-emerald-800' : ($selectedRefugee->status === 'pindah' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ $selectedRefugee->status_label }}
                            </span>
                        </div>

                        <!-- Ringkasan Anggota & Kelompok Rentan -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 bg-slate-50 p-3.5 rounded-xl border border-slate-100 text-xs">
                            <div>
                                <span class="text-slate-400 block">Total Anggota</span>
                                <span class="font-bold text-slate-800 text-sm">{{ $selectedRefugee->members }} jiwa</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Balita</span>
                                <span class="font-semibold text-slate-700">{{ $selectedRefugee->infants }} jiwa</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Lansia</span>
                                <span class="font-semibold text-slate-700">{{ $selectedRefugee->elderly }} jiwa</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Ibu Hamil/Menyusui</span>
                                <span class="font-semibold text-slate-700">{{ $selectedRefugee->pregnant }} jiwa</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Disabilitas</span>
                                <span class="font-semibold text-slate-700">{{ $selectedRefugee->disabled }} jiwa</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Posko Saat Ini</span>
                                <span class="font-semibold text-slate-700 truncate block" title="{{ $selectedRefugee->shelter?->name }}">
                                    {{ $selectedRefugee->shelter ? str_replace(['Posko Utama ', 'Posko Evakuasi ', 'Posko Darurat '], '', $selectedRefugee->shelter->name) : '-' }}
                                </span>
                            </div>
                        </div>

                        <!-- Riwayat Masuk, Pindah, dan Pulang -->
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">
                                Riwayat Pergerakan Posko
                            </h3>
                            <div class="space-y-3">
                                @forelse ($selectedRefugee->movements as $movement)
                                    <div class="flex items-start space-x-3 p-3 rounded-xl bg-slate-50/80 border border-slate-100 text-xs">
                                        <div class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ $movement->type === 'masuk' ? 'bg-emerald-500' : ($movement->type === 'pindah' ? 'bg-blue-500' : 'bg-slate-400') }}"></div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-slate-800">
                                                {{ $movement->type_label }}
                                            </p>
                                            <p class="text-slate-500 mt-0.5">
                                                @if ($movement->type === 'masuk' && $movement->toShelter)
                                                    Check-in di <span class="font-medium text-slate-700">{{ $movement->toShelter->name }}</span>
                                                @elseif ($movement->type === 'pindah')
                                                    Dari <span class="font-medium text-slate-700">{{ $movement->fromShelter?->name ?? '-' }}</span>
                                                    ke <span class="font-medium text-slate-700">{{ $movement->toShelter?->name ?? '-' }}</span>
                                                @elseif ($movement->type === 'pulang' && $movement->fromShelter)
                                                    Pulang dari <span class="font-medium text-slate-700">{{ $movement->fromShelter->name }}</span>
                                                @endif
                                            </p>
                                            <p class="text-[11px] text-slate-400 mt-1">
                                                {{ $movement->created_at ? $movement->created_at->format('d M Y, H:i') : '-' }}
                                                @if ($movement->creator)
                                                    • Dicatat oleh {{ $movement->creator->name }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic">Belum ada catatan riwayat pergerakan.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Bottom Actions -->
                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 gap-3">
                            <button 
                                type="button" 
                                wire:click="closeDetailModal"
                                class="flex-1 py-2 px-4 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 transition cursor-pointer"
                            >
                                Tutup
                            </button>
                            @if ($canManage && $selectedRefugee->status === 'di_posko')
                                <button 
                                    type="button" 
                                    wire:click="openEditModal({{ $selectedRefugee->id }})"
                                    class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition cursor-pointer"
                                >
                                    Ubah Data
                                </button>
                            @endif
                        </div>
                    </div>

                {{-- MODE 3 (DEFAULT): CHECK-IN PENGUNGSI BARU --}}
                @else
                    <div class="mb-4">
                        <h2 class="text-base font-bold text-slate-900">
                            Check-in Pengungsi Baru
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Daftarkan kepala keluarga baru yang masuk ke posko evakuasi.
                        </p>
                    </div>

                    @if (!empty($formWarnings))
                        <div class="mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs space-y-1">
                            @foreach ($formWarnings as $w)
                                <p class="font-medium">{{ $w['message'] }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form wire:submit.prevent="checkIn" class="space-y-4">
                        
                        @if ($accessibleShelters->count() > 1)
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Posko evakuasi</label>
                                <select 
                                    wire:model.live="shelter_id"
                                    required
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                >
                                    <option value="">-- Pilih posko --</option>
                                    @foreach ($accessibleShelters as $s)
                                        <option value="{{ $s->id }}">
                                            {{ $s->name }} (sisa {{ $s->availableCapacity() }} jiwa)
                                        </option>
                                    @endforeach
                                </select>
                                @error('shelter_id') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <!-- Row 1: Nama Kepala Keluarga & NIK -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Nama kepala keluarga</label>
                                <input 
                                    type="text" 
                                    wire:model="head_name" 
                                    required
                                    placeholder="Contoh: Ujang Suryana"
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('head_name') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">NIK (opsional)</label>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.400ms="nik" 
                                    maxlength="16"
                                    placeholder="16 digit NIK"
                                    class="w-full py-2 px-3 text-xs sm:text-sm font-mono bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('nik') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Row 2: Jumlah Anggota & Balita -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Jumlah anggota</label>
                                <input 
                                    type="number" 
                                    min="1"
                                    wire:model.live="members" 
                                    required
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('members') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Balita</label>
                                <input 
                                    type="number" 
                                    min="0"
                                    wire:model="infants" 
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('infants') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Row 3: Lansia & Ibu Hamil/Menyusui -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Lansia</label>
                                <input 
                                    type="number" 
                                    min="0"
                                    wire:model="elderly" 
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('elderly') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Ibu hamil/menyusui</label>
                                <input 
                                    type="number" 
                                    min="0"
                                    wire:model="pregnant" 
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('pregnant') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Row 4: Penyandang Disabilitas -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Penyandang disabilitas</label>
                            <input 
                                type="number" 
                                min="0"
                                wire:model="disabled" 
                                class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            />
                            @error('disabled') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Okupansi posko bertambah otomatis dengan peringatan bila melebihi kapasitas atau NIK masih aktif di posko lain.
                        </p>

                        @if ($canManage)
                            <div class="grid grid-cols-2 gap-3 pt-1">
                                <button 
                                    type="button" 
                                    wire:click="closeCreateModal"
                                    class="w-full py-2.5 px-4 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 transition cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="submit" 
                                    class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition cursor-pointer"
                                >
                                    Check-in Pengungsi
                                </button>
                            </div>
                        @endif

                    </form>
                @endif

            </div>
        </div>

    </div>

    <!-- Modal Konfirmasi Hapus (Sesuai Mockup Bagian Bawah) -->
    @if ($showDeleteModal && $selectedRefugee)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" wire:click="closeDeleteModal" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6">
                    <h3 class="text-base font-bold text-slate-900">
                        Hapus data "{{ $selectedRefugee->head_name }}"?
                    </h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Hanya untuk data salah input yang belum memiliki catatan penyaluran atau pemeriksaan. Okupansi posko dikoreksi otomatis.
                    </p>

                    @if (!$selectedRefugee->canBeDeleted())
                        <div class="mt-4 p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700">
                            Data ini tidak dapat dihapus karena sudah memiliki catatan <strong>{{ implode(' dan ', $selectedRefugee->deletionBlockReasons()) }}</strong>.
                        </div>
                    @endif

                    @error('delete')
                        <div class="mt-3 p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="mt-4">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alasan (wajib)</label>
                        <select 
                            wire:model="delete_reason"
                            class="w-full py-2.5 px-3 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                        >
                            <option value="Data ganda / salah input">Data ganda / salah input</option>
                            <option value="Kepala keluarga tercatat dua kali">Kepala keluarga tercatat dua kali</option>
                            <option value="Salah pilih lokasi posko evakuasi">Salah pilih lokasi posko evakuasi</option>
                            <option value="Kesalahan input jumlah anggota keluarga">Kesalahan input jumlah anggota keluarga</option>
                        </select>
                        @error('delete_reason') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-6 flex items-center justify-end space-x-3">
                        <button 
                            type="button" 
                            wire:click="closeDeleteModal" 
                            class="px-4 py-2 bg-white text-slate-700 hover:bg-slate-100 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="button" 
                            wire:click="confirmDelete" 
                            @disabled(!$selectedRefugee->canBeDeleted())
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-300 disabled:cursor-not-allowed text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition cursor-pointer"
                        >
                            Hapus Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
