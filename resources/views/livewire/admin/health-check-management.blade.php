<div class="space-y-6">

    <!-- Toast Notification -->
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

    <!-- Breadcrumb -->
    <div class="text-xs font-medium text-slate-400">
        <span>Beranda</span>
        <span class="mx-1.5">/</span>
        <span class="text-slate-600">Pemeriksaan</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                Pemeriksaan &amp; Triase
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">
                PBI-08 · Catat keluhan, tanda vital, dan kategori triase pengungsi
            </p>
        </div>

        <div class="flex items-center space-x-3">
            @if ($accessibleShelters->count() > 1)
                @php
                    $headerShelterOptions = $accessibleShelters->map(fn ($s) => [
                        'id' => (string) $s->id,
                        'name' => $s->name,
                        'address' => $s->address ?? '',
                        'available' => $s->availableCapacity(),
                    ])->values()->all();
                @endphp
                <div 
                    x-data="{
                        open: false,
                        search: '',
                        options: @js($headerShelterOptions),
                        get selectedId() { return String($wire.shelterFilter ?? ''); },
                        get selectedOption() {
                            return this.options.find(o => o.id === this.selectedId) || null;
                        },
                        get filteredOptions() {
                            const q = this.search.trim().toLowerCase();
                            if (!q) return this.options;
                            return this.options.filter(o =>
                                o.name.toLowerCase().includes(q) ||
                                (o.address && o.address.toLowerCase().includes(q))
                            );
                        },
                        selectShelter(id) {
                            $wire.call('selectShelter', id ? parseInt(id) : null);
                            this.search = '';
                            this.open = false;
                        }
                    }"
                    @click.outside="open = false; search = ''"
                    @keydown.escape.window="open = false; search = ''"
                    class="relative w-full sm:w-72 md:w-80"
                >
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            x-model="search"
                            @focus="open = true"
                            @input="open = true"
                            :placeholder="selectedOption ? selectedOption.name : 'Semua Posko (Cari posko...)'"
                            :class="selectedOption && !open ? 'placeholder:text-slate-800 placeholder:font-semibold' : 'placeholder:text-slate-500'"
                            class="w-full py-2.5 pl-9 pr-9 text-xs sm:text-sm bg-white text-slate-800 font-medium rounded-xl border border-slate-200 shadow-2xs focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                        />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5">
                            <template x-if="selectedId !== ''">
                                <button
                                    type="button"
                                    @click.stop="selectShelter('')"
                                    title="Kembali ke Semua Posko"
                                    class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </template>
                            <template x-if="selectedId === ''">
                                <button
                                    type="button"
                                    @click="open = !open"
                                    class="p-1 text-slate-400 hover:text-slate-600 transition cursor-pointer"
                                >
                                    <svg class="w-4 h-4 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Search Results Dropdown -->
                    <div
                        x-show="open"
                        x-transition.opacity.duration.150ms
                        style="display: none;"
                        class="absolute z-40 mt-1.5 w-full bg-white rounded-xl border border-slate-200 shadow-lg max-h-72 overflow-y-auto divide-y divide-slate-100"
                    >
                        <button
                            type="button"
                            @click="selectShelter('')"
                            class="w-full px-3.5 py-2.5 text-left hover:bg-slate-50 transition flex items-center justify-between cursor-pointer"
                            :class="selectedId === '' ? 'bg-blue-50/70 text-blue-700 font-semibold' : 'text-slate-700'"
                        >
                            <div>
                                <span class="text-xs sm:text-sm block">Semua Posko</span>
                                <span class="text-[11px] text-slate-400 block">Tampilkan gabungan seluruh posko</span>
                            </div>
                            <template x-if="selectedId === ''">
                                <span class="text-[10px] font-semibold text-blue-600">Dipilih</span>
                            </template>
                        </button>

                        <template x-for="item in filteredOptions" :key="item.id">
                            <button
                                type="button"
                                @click="selectShelter(item.id)"
                                class="w-full px-3.5 py-2.5 text-left hover:bg-slate-50 transition flex items-start justify-between gap-2 cursor-pointer"
                                :class="selectedId === item.id ? 'bg-blue-50/70' : ''"
                            >
                                <div class="min-w-0 flex-1">
                                    <span class="text-xs sm:text-sm font-medium block truncate" :class="selectedId === item.id ? 'text-blue-700 font-semibold' : 'text-slate-800'" x-text="item.name"></span>
                                    <span class="text-[11px] text-slate-400 block truncate" x-text="item.address"></span>
                                </div>
                            </button>
                        </template>

                        <div x-show="filteredOptions.length === 0" class="px-3.5 py-4 text-center text-xs text-slate-400">
                            Posko tidak ditemukan untuk "<span class="font-medium text-slate-600" x-text="search"></span>"
                        </div>
                    </div>
                </div>
            @endif

            @if ($canManage)
                <button 
                    type="button"
                    wire:click="openCreateModal"
                    class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition duration-150 cursor-pointer"
                >
                    <span>Periksa Pengungsi</span>
                </button>
            @endif
        </div>
    </div>

    <!-- 3 Key Summary Metric Cards (Sesuai Mockup PBI-08) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <!-- Card 1: Diperiksa hari ini -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Diperiksa hari ini</span>
            <div class="mt-1.5 flex items-baseline space-x-1.5">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $summary['totals']['total'] }}
                </span>
            </div>
            <p class="mt-1.5 text-xs text-slate-400">
                {{ $currentShelter ? str_replace(['Posko Utama ', 'Posko Evakuasi ', 'Posko Darurat '], '', $currentShelter->name) : 'Semua Posko' }}
            </p>
        </div>

        <!-- Card 2: Triase Merah -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Triase Merah</span>
            <div class="mt-1.5 flex items-baseline space-x-1.5">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $summary['totals']['merah'] }}
                </span>
            </div>
            <p class="mt-1.5 text-xs text-slate-400">
                {{ $summary['unreferred_merah'] ?? 0 }} belum dirujuk
            </p>
        </div>

        <!-- Card 3: Triase Kuning -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Triase Kuning</span>
            <div class="mt-1.5 flex items-baseline space-x-1.5">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $summary['totals']['kuning'] }}
                </span>
            </div>
            <p class="mt-1.5 text-xs text-slate-400">
                perlu observasi
            </p>
        </div>

    </div>

    <!-- Main Content Split Layout: Table (Left) + Dynamic Action Panel (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Health Checks Table & Daily Shelter Summary -->
        <div class="lg:col-span-7 xl:col-span-7 space-y-4">

            <!-- Health Checks Table Card (Sesuai Mockup) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50/90 text-slate-500 text-[11px] font-semibold uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3.5 px-5">Pengungsi &amp; Keluhan</th>
                                <th class="py-3.5 px-4">Waktu</th>
                                <th class="py-3.5 px-4">Triase</th>
                                <th class="py-3.5 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($checks as $check)
                                @php
                                    $isSelected = $healthCheckId === $check->id;
                                    $triageBadge = match ($check->triage) {
                                        'merah'  => ['label' => 'Merah', 'classes' => 'bg-red-100/90 text-red-700'],
                                        'kuning' => ['label' => 'Kuning', 'classes' => 'bg-amber-100/90 text-amber-800'],
                                        'hijau'  => ['label' => 'Hijau', 'classes' => 'bg-emerald-100/90 text-emerald-800'],
                                        default  => ['label' => ucfirst((string) $check->triage), 'classes' => 'bg-slate-100 text-slate-600'],
                                    };
                                @endphp
                                <tr 
                                    wire:key="health-check-{{ $check->id }}"
                                    class="transition {{ $isSelected ? 'bg-blue-50/75' : 'hover:bg-slate-50/60' }}"
                                >
                                    <!-- Pengungsi & Keluhan -->
                                    <td class="py-3.5 px-5">
                                        <button 
                                            type="button"
                                            wire:click="openHistoryModal({{ $check->refugee_id }})"
                                            class="text-left group focus:outline-none"
                                        >
                                            <span class="font-semibold text-slate-900 group-hover:text-blue-600 transition block leading-snug">
                                                {{ $check->refugee?->head_name ?? '-' }} · {{ \Illuminate\Support\Str::limit($check->complaint, 38) }}
                                            </span>
                                        </button>
                                    </td>

                                    <!-- Waktu -->
                                    <td class="py-3.5 px-4 font-mono text-xs text-slate-600">
                                        {{ $check->checked_at?->format('H.i') }}
                                    </td>

                                    <!-- Triase Badge -->
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $triageBadge['classes'] }}">
                                            {{ $triageBadge['label'] }}
                                        </span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3.5 px-5 text-right">
                                        <div class="inline-flex items-center justify-end space-x-2">
                                            @if ($canManage)
                                                <button 
                                                    type="button"
                                                    wire:click="openEditModal({{ $check->id }})"
                                                    class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg border border-slate-200 shadow-2xs transition cursor-pointer"
                                                >
                                                    Ubah
                                                </button>
                                                <button 
                                                    type="button"
                                                    wire:click="openDeleteModal({{ $check->id }})"
                                                    class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition cursor-pointer"
                                                >
                                                    Hapus
                                                </button>
                                            @else
                                                <button 
                                                    type="button"
                                                    wire:click="openHistoryModal({{ $check->refugee_id }})"
                                                    class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg border border-slate-200 shadow-2xs transition cursor-pointer"
                                                >
                                                    Riwayat
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-slate-400">
                                        <p class="text-sm font-semibold text-slate-700">Belum ada catatan pemeriksaan ditemukan</p>
                                        <p class="text-xs text-slate-400 mt-1">Pilih tombol Periksa Pengungsi untuk mencatat hasil pemeriksaan baru.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Access Control Footnote (Sesuai Mockup) -->
                <div class="px-5 py-3.5 border-t border-slate-100 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <p class="text-xs text-slate-400">
                        Data kesehatan hanya dapat diakses nakes dan koordinator posko terkait
                    </p>
                </div>

                @if ($checks->hasPages())
                    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                        {{ $checks->links() }}
                    </div>
                @endif
            </div>

        </div>

        <!-- Right Column: Contextual Side Panel (Edit / Create / History) -->
        <div class="lg:col-span-5 xl:col-span-5">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6">

                {{-- MODE 1: UBAH PEMERIKSAAN (Sesuai Mockup Panel Kanan) --}}
                @if ($panelMode === 'edit' && $selectedCheck)
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-slate-900">
                            Ubah pemeriksaan: {{ $selectedCheck->refugee?->head_name }}
                        </h2>
                        <button 
                            type="button"
                            wire:click="openHistoryModal({{ $selectedCheck->refugee_id }})"
                            class="text-xs font-semibold text-blue-600 hover:text-blue-700"
                        >
                            Lihat Riwayat
                        </button>
                    </div>

                    @error('health_check')
                        <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs">
                            {{ $message }}
                        </div>
                    @enderror

                    <form wire:submit.prevent="updateHealthCheck" class="space-y-4">
                        
                        <!-- Pengungsi (Terkunci saat Ubah) -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Pengungsi</label>
                            <div class="w-full py-2.5 px-3.5 text-xs sm:text-sm bg-slate-50 text-slate-800 font-medium rounded-xl border border-slate-200 flex items-center justify-between">
                                <span>{{ $selectedCheck->refugee?->head_name }}</span>
                                <span class="text-[11px] text-slate-400 font-normal">
                                    {{ $selectedCheck->refugee?->shelter ? str_replace(['Posko Utama ', 'Posko Evakuasi ', 'Posko Darurat '], '', $selectedCheck->refugee->shelter->name) : '' }}
                                </span>
                            </div>
                        </div>

                        <!-- Keluhan -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Keluhan</label>
                            <input 
                                type="text" 
                                wire:model="complaint" 
                                required
                                placeholder="Contoh: Sesak napas sejak semalam, riwayat asma"
                                class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            />
                            @error('complaint') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Suhu (°C) & Tekanan darah -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Suhu (°C)</label>
                                <input 
                                    type="text" 
                                    inputmode="decimal"
                                    wire:model="temperature" 
                                    required
                                    placeholder="37,9"
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('temperature') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Tekanan darah</label>
                                <input 
                                    type="text" 
                                    wire:model="blood_pressure" 
                                    required
                                    placeholder="150/95"
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('blood_pressure') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Nadi (x/menit) & Triase -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Nadi (x/menit)</label>
                                <input 
                                    type="number" 
                                    min="20"
                                    max="250"
                                    wire:model="pulse" 
                                    required
                                    placeholder="112"
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('pulse') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Triase</label>
                                <select 
                                    wire:model.live="triage"
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                >
                                    <option value="merah">Merah</option>
                                    <option value="kuning">Kuning</option>
                                    <option value="hijau">Hijau</option>
                                </select>
                                @error('triage') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Catatan / tindakan (opsional) -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Catatan (opsional)</label>
                            <input 
                                type="text" 
                                wire:model="action" 
                                placeholder="Tindakan medis atau obat yang diberikan"
                                class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            />
                            @error('action') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Alert Box Triase Merah (Sesuai Mockup) -->
                        @if ($triage === 'merah')
                            <div class="p-3.5 rounded-xl bg-red-50 border border-red-200/80 text-xs space-y-1">
                                <p class="font-bold text-red-700">Triase Merah</p>
                                <p class="text-red-700 leading-relaxed">
                                    Koordinator posko sudah dinotifikasi. Lanjutkan dengan membuat rujukan medis (PBI-09).
                                </p>
                            </div>
                        @endif

                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Dapat diubah maksimal 24 jam setelah dibuat oleh nakes pembuat.
                        </p>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <button 
                                type="button" 
                                wire:click="closeFormPanel"
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

                {{-- MODE 2: RIWAYAT PEMERIKSAAN PER PENGUNGSI & RINGKASAN POSKO (AC 2) --}}
                @elseif ($panelMode === 'history')
                    <div class="space-y-5">
                        @if ($historyRefugee)
                            <div class="flex items-start justify-between">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900">
                                        Riwayat: {{ $historyRefugee->head_name }}
                                    </h2>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $historyRefugee->shelter?->name ?? '-' }} · {{ $historyRefugee->healthChecks->count() }} pemeriksaan
                                    </p>
                                </div>
                                @if ($canManage)
                                    <button 
                                        type="button"
                                        wire:click="openCreateModal({{ $historyRefugee->id }})"
                                        class="text-xs font-semibold text-blue-600 hover:text-blue-700"
                                    >
                                        + Periksa Baru
                                    </button>
                                @endif
                            </div>

                            <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                                @forelse ($historyRefugee->healthChecks as $item)
                                    @php
                                        $itemBadge = match ($item->triage) {
                                            'merah'  => 'bg-red-100 text-red-700',
                                            'kuning' => 'bg-amber-100 text-amber-800',
                                            default  => 'bg-emerald-100 text-emerald-800',
                                        };
                                    @endphp
                                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-slate-800">{{ $item->checked_at?->format('d/m/Y H.i') }}</span>
                                            <span class="px-2 py-0.5 rounded-full font-semibold {{ $itemBadge }}">
                                                {{ ucfirst($item->triage) }}
                                            </span>
                                        </div>
                                        <p class="text-slate-700 font-medium">{{ $item->complaint }}</p>
                                        <p class="text-slate-500 font-mono text-[11px]">
                                            Suhu {{ $item->temperature }}°C · TD {{ $item->blood_pressure }} · Nadi {{ $item->pulse }}x/menit
                                        </p>
                                        @if ($item->action)
                                            <p class="text-slate-500 text-[11px]">Catatan: {{ $item->action }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400">Belum ada riwayat pemeriksaan untuk pengungsi ini.</p>
                                @endforelse
                            </div>
                        @endif

                        <!-- Ringkasan Triase Harian per Posko -->
                        <div class="space-y-2.5 {{ $historyRefugee ? 'pt-3 border-t border-slate-100' : '' }}">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                Ringkasan Triase Harian per Posko
                            </h3>
                            @foreach ($summary['shelters'] as $s)
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-800 truncate pr-2">{{ $s['shelter_name'] }}</span>
                                    <div class="flex items-center space-x-2 shrink-0 font-semibold">
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">H: {{ $s['hijau'] }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">K: {{ $s['kuning'] }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700">M: {{ $s['merah'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                {{-- MODE 3 (DEFAULT NAKES): TAMBAH PEMERIKSAAN BARU --}}
                @else
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-slate-900">
                            Periksa Pengungsi
                        </h2>
                    </div>

                    <form wire:submit.prevent="storeHealthCheck" class="space-y-4">
                        
                        <!-- Pengungsi Searchable Combobox (Sesuai Style Refugee Management) -->
                        @php
                            $refugeeOptions = $selectableRefugees->map(fn ($r) => [
                                'id' => (string) $r->id,
                                'head_name' => $r->head_name,
                                'members' => (int) $r->members,
                                'nik' => $r->masked_nik,
                                'shelter_name' => $r->shelter ? str_replace(['Posko Utama ', 'Posko Evakuasi ', 'Posko Darurat '], '', $r->shelter->name) : '',
                                'label' => $r->head_name . ' (' . $r->members . ' jiwa)',
                            ])->values()->all();
                        @endphp
                        <div
                            x-data="{
                                open: false,
                                search: '',
                                options: @js($refugeeOptions),
                                get selectedId() { return String($wire.refugee_id ?? ''); },
                                get selectedOption() {
                                    return this.options.find(o => o.id === this.selectedId) || null;
                                },
                                get filteredOptions() {
                                    const q = this.search.trim().toLowerCase();
                                    if (!q) return this.options;
                                    return this.options.filter(o =>
                                        o.head_name.toLowerCase().includes(q) ||
                                        (o.nik && o.nik.toLowerCase().includes(q))
                                    );
                                },
                                selectRefugee(id) {
                                    $wire.call('selectRefugee', parseInt(id));
                                    this.search = '';
                                    this.open = false;
                                },
                                clearRefugee() {
                                    $wire.call('clearSelectedRefugee');
                                    this.search = '';
                                    this.open = false;
                                }
                            }"
                            @click.outside="open = false; search = ''"
                            @keydown.escape.window="open = false; search = ''"
                            class="relative"
                        >
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Pengungsi</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z" />
                                    </svg>
                                </span>
                                <input
                                    type="text"
                                    x-model="search"
                                    @focus="open = true"
                                    @input="open = true"
                                    :placeholder="selectedOption ? selectedOption.label : 'Cari nama kepala keluarga atau NIK...'"
                                    :class="selectedOption && !open ? 'placeholder:text-slate-800 placeholder:font-medium' : 'placeholder:text-slate-400'"
                                    class="w-full py-2 pl-9 pr-9 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2.5">
                                    <template x-if="selectedId !== ''">
                                        <button
                                            type="button"
                                            @click.stop="clearRefugee()"
                                            title="Hapus pilihan pengungsi"
                                            class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </template>
                                    <template x-if="selectedId === ''">
                                        <button
                                            type="button"
                                            @click="open = !open"
                                            class="p-1 text-slate-400 hover:text-slate-600 transition cursor-pointer"
                                        >
                                            <svg class="w-4 h-4 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div
                                x-show="open"
                                x-transition.opacity.duration.150ms
                                style="display: none;"
                                class="absolute z-40 mt-1.5 w-full bg-white rounded-xl border border-slate-200 shadow-lg max-h-60 overflow-y-auto divide-y divide-slate-100"
                            >
                                <template x-for="item in filteredOptions" :key="item.id">
                                    <button
                                        type="button"
                                        @click="selectRefugee(item.id)"
                                        class="w-full px-3.5 py-2.5 text-left hover:bg-slate-50 transition flex items-start justify-between gap-2 cursor-pointer"
                                        :class="selectedId === item.id ? 'bg-blue-50/70' : ''"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <span class="text-xs sm:text-sm font-medium block truncate" :class="selectedId === item.id ? 'text-blue-700 font-semibold' : 'text-slate-800'" x-text="item.head_name + ' (' + item.members + ' jiwa)'"></span>
                                            <span class="text-[11px] text-slate-400 font-mono block truncate" x-text="item.nik + (item.shelter_name ? ' · ' + item.shelter_name : '')"></span>
                                        </div>
                                        <template x-if="selectedId === item.id">
                                            <span class="text-[10px] font-semibold text-blue-600 shrink-0">Dipilih</span>
                                        </template>
                                    </button>
                                </template>

                                <div x-show="filteredOptions.length === 0" class="px-3.5 py-4 text-center text-xs text-slate-400">
                                    Pengungsi tidak ditemukan untuk "<span class="font-medium text-slate-600" x-text="search"></span>"
                                </div>
                            </div>
                            @error('refugee_id') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Keluhan -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Keluhan</label>
                            <input 
                                type="text" 
                                wire:model="complaint" 
                                required
                                placeholder="Sesak napas sejak semalam, riwayat asma"
                                class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            />
                            @error('complaint') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Suhu (°C) & Tekanan darah -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Suhu (°C)</label>
                                <input 
                                    type="text" 
                                    inputmode="decimal"
                                    wire:model="temperature" 
                                    required
                                    placeholder="37,9"
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('temperature') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Tekanan darah</label>
                                <input 
                                    type="text" 
                                    wire:model="blood_pressure" 
                                    required
                                    placeholder="150/95"
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('blood_pressure') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Nadi (x/menit) & Triase -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Nadi (x/menit)</label>
                                <input 
                                    type="number" 
                                    min="20"
                                    max="250"
                                    wire:model="pulse" 
                                    required
                                    placeholder="112"
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                @error('pulse') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Triase</label>
                                <select 
                                    wire:model.live="triage"
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                >
                                    <option value="hijau">Hijau</option>
                                    <option value="kuning">Kuning</option>
                                    <option value="merah">Merah</option>
                                </select>
                                @error('triage') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Catatan (opsional) -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Catatan (opsional)</label>
                            <input 
                                type="text" 
                                wire:model="action" 
                                placeholder="Tindakan medis atau obat yang diberikan"
                                class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            />
                            @error('action') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Alert Box Triase Merah (Sesuai Mockup) -->
                        @if ($triage === 'merah')
                            <div class="p-3.5 rounded-xl bg-red-50 border border-red-200/80 text-xs space-y-1">
                                <p class="font-bold text-red-700">Triase Merah</p>
                                <p class="text-red-700 leading-relaxed">
                                    Koordinator posko sudah dinotifikasi. Lanjutkan dengan membuat rujukan medis (PBI-09).
                                </p>
                            </div>
                        @endif

                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Dapat diubah maksimal 24 jam setelah dibuat oleh nakes pembuat.
                        </p>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <button 
                                type="button" 
                                wire:click="closeFormPanel"
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
                @endif

            </div>
        </div>

    </div>

    <!-- Modal Konfirmasi Hapus Catatan Pemeriksaan (Sesuai Mockup Bagian Bawah) -->
    @if ($showDeleteModal && $selectedCheck)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" wire:click="closeDeleteModal" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6">
                    <h3 class="text-base font-bold text-slate-900">
                        Hapus catatan pemeriksaan?
                    </h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Catatan yang salah input dihapus (soft delete) dan tetap tersimpan untuk audit. Catatan yang sudah memiliki rujukan tidak dapat dihapus.
                    </p>

                    @if ($selectedCheck->hasReferrals())
                        <div class="mt-4 p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700">
                            Catatan pemeriksaan ini tidak dapat dihapus karena sudah memiliki <strong>rujukan medis</strong>.
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
                            <option value="Salah memilih pengungsi">Salah memilih pengungsi</option>
                            <option value="Salah input data pemeriksaan">Salah input data pemeriksaan</option>
                            <option value="Catatan pemeriksaan ganda">Catatan pemeriksaan ganda</option>
                            <option value="Salah input tanda vital">Salah input tanda vital</option>
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
                            @disabled($selectedCheck->hasReferrals())
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-300 disabled:cursor-not-allowed text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition cursor-pointer"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

