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
                <button type="button" wire:click="dismissNotification" class="text-slate-400 hover:text-slate-600 p-0.5 cursor-pointer">
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
        <span class="text-slate-600">Rujukan Medis</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                Rujukan Medis
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">
                PBI-09 · Rujuk pengungsi gawat ke fasilitas kesehatan dan pantau statusnya
            </p>
        </div>

        <div class="flex items-center space-x-3">
            @if ($canCreate)
                <button
                    type="button"
                    wire:click="openCreatePanel"
                    class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition duration-150 cursor-pointer"
                >
                    <span>Buat Rujukan</span>
                </button>
            @endif
        </div>
    </div>

    <!-- 4 Status Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Diajukan</span>
            <span class="mt-1.5 block text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight tabular-nums">
                {{ $summary['diajukan'] }}
            </span>
            <p class="mt-1 text-xs text-amber-700">Menunggu keberangkatan</p>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Dalam Perjalanan</span>
            <span class="mt-1.5 block text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight tabular-nums">
                {{ $summary['dalam_perjalanan'] }}
            </span>
            <p class="mt-1 text-xs text-blue-700">Menuju fasilitas kesehatan</p>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Diterima Faskes</span>
            <span class="mt-1.5 block text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight tabular-nums">
                {{ $summary['diterima_faskes'] }}
            </span>
            <p class="mt-1 text-xs text-indigo-700">Dalam penanganan faskes</p>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Selesai</span>
            <span class="mt-1.5 block text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight tabular-nums">
                {{ $summary['selesai'] }}
            </span>
            <p class="mt-1 text-xs text-slate-400">
                {{ $summary['unreferred_merah'] }} triase merah belum dirujuk
            </p>
        </div>
    </div>

    <!-- Main Content Split Layout (Sesuai Mockup: Tabel & Filter di Kiri + Panel Kontekstual di Kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Filter Status & Posko + Referrals Table -->
        <div class="lg:col-span-7 xl:col-span-7 space-y-4">

            <!-- Filter Bar: Status & Posko (Sesuai Mockup) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Filter Status -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status</label>
                    <select
                        wire:model.live="statusFilter"
                        class="w-full py-2.5 px-3.5 text-xs sm:text-sm bg-white text-slate-800 font-medium rounded-xl border border-slate-200 shadow-2xs focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                    >
                        <option value="">Semua status</option>
                        <option value="diajukan">Diajukan</option>
                        <option value="dalam_perjalanan">Dalam Perjalanan</option>
                        <option value="diterima_faskes">Diterima Faskes</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>

                <!-- Filter Posko (Searchable Combobox style terbaru) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Posko</label>
                    @if ($accessibleShelters->count() > 1)
                        @php
                            $shelterOptions = $accessibleShelters->map(fn ($s) => [
                                'id' => (string) $s->id,
                                'name' => $s->name,
                                'address' => $s->address ?? '',
                            ])->values()->all();
                        @endphp
                        <div
                            x-data="{
                                open: false,
                                search: '',
                                options: @js($shelterOptions),
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
                                pick(id) {
                                    $wire.call('selectShelter', id ? parseInt(id) : null);
                                    this.search = '';
                                    this.open = false;
                                }
                            }"
                            @click.outside="open = false; search = ''"
                            @keydown.escape.window="open = false; search = ''"
                            class="relative"
                        >
                            <div class="relative">
                                <input
                                    type="text"
                                    x-model="search"
                                    @focus="open = true"
                                    @input="open = true"
                                    :placeholder="selectedOption ? selectedOption.name : 'Semua Posko'"
                                    :class="selectedOption && !open ? 'placeholder:text-slate-800 placeholder:font-semibold' : 'placeholder:text-slate-500'"
                                    class="w-full py-2.5 pl-3.5 pr-9 text-xs sm:text-sm bg-white text-slate-800 font-medium rounded-xl border border-slate-200 shadow-2xs focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2.5">
                                    <template x-if="selectedId !== ''">
                                        <button
                                            type="button"
                                            @click.stop="pick('')"
                                            title="Semua Posko"
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
                                class="absolute z-40 mt-1.5 w-full bg-white rounded-xl border border-slate-200 shadow-lg max-h-64 overflow-y-auto divide-y divide-slate-100"
                            >
                                <button
                                    type="button"
                                    @click="pick('')"
                                    class="w-full px-3.5 py-2.5 text-left hover:bg-slate-50 transition flex items-center justify-between cursor-pointer"
                                    :class="selectedId === '' ? 'bg-blue-50/70 text-blue-700 font-semibold' : 'text-slate-700'"
                                >
                                    <span class="text-xs sm:text-sm">Semua Posko</span>
                                    <template x-if="selectedId === ''">
                                        <span class="text-[10px] font-semibold text-blue-600">Dipilih</span>
                                    </template>
                                </button>
                                <template x-for="item in filteredOptions" :key="item.id">
                                    <button
                                        type="button"
                                        @click="pick(item.id)"
                                        class="w-full px-3.5 py-2.5 text-left hover:bg-slate-50 transition cursor-pointer"
                                        :class="selectedId === item.id ? 'bg-blue-50/70' : ''"
                                    >
                                        <span class="text-xs sm:text-sm font-medium block truncate" :class="selectedId === item.id ? 'text-blue-700 font-semibold' : 'text-slate-800'" x-text="item.name"></span>
                                        <span class="text-[11px] text-slate-400 block truncate" x-text="item.address"></span>
                                    </button>
                                </template>
                                <div x-show="filteredOptions.length === 0" class="px-3.5 py-4 text-center text-xs text-slate-400">
                                    Posko tidak ditemukan
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="w-full py-2.5 px-3.5 text-xs sm:text-sm bg-slate-50 text-slate-700 font-medium rounded-xl border border-slate-200 truncate">
                            {{ $accessibleShelters->first()?->name ?? 'Belum ada posko terhubung' }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Referrals Table Card (Sesuai Mockup) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50/90 text-slate-500 text-[11px] font-semibold uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3.5 px-5">Pasien</th>
                                <th class="py-3.5 px-4">Faskes</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($referrals as $item)
                                @php
                                    $isSelected = $referralId === $item->id;
                                    $statusBadge = match ($item->status) {
                                        'diajukan'         => 'bg-amber-100/90 text-amber-800',
                                        'dalam_perjalanan' => 'bg-blue-100/90 text-blue-700',
                                        'diterima_faskes'  => 'bg-indigo-100/90 text-indigo-700',
                                        'selesai'          => 'bg-emerald-100/90 text-emerald-800',
                                        'dibatalkan'       => 'bg-slate-100 text-slate-600',
                                        default            => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp
                                <tr
                                    wire:key="referral-{{ $item->id }}"
                                    wire:click="openDetailPanel({{ $item->id }})"
                                    class="transition cursor-pointer group {{ $isSelected ? 'bg-blue-50/75' : 'hover:bg-slate-50/80' }}"
                                >
                                    <!-- Pasien -->
                                    <td class="py-3.5 px-5">
                                        <span class="font-semibold text-slate-900 group-hover:text-blue-600 transition block leading-snug">
                                            {{ $item->healthCheck?->refugee?->head_name ?? '-' }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 block mt-0.5">
                                            {{ $item->healthCheck?->refugee?->shelter?->name ?? '-' }}
                                            @if ($item->healthCheck?->triage === 'merah')
                                                · <span class="text-red-600 font-semibold">Triase Merah</span>
                                            @endif
                                        </span>
                                    </td>

                                    <!-- Faskes -->
                                    <td class="py-3.5 px-4 text-slate-700">
                                        <span class="block font-medium leading-snug">{{ $item->facility_name }}</span>
                                        @if ($item->transport_needed)
                                            <span class="text-[11px] text-slate-400 block">Butuh transportasi</span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap {{ $statusBadge }}">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3.5 px-5 text-right" wire:click.stop>
                                        <div class="inline-flex items-center justify-end space-x-2">
                                            <button
                                                type="button"
                                                wire:click.stop="openDetailPanel({{ $item->id }})"
                                                class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg border border-slate-200 shadow-2xs transition cursor-pointer"
                                            >
                                                Detail
                                            </button>

                                            @if ($canUpdate && $item->canUpdateStatus())
                                                <button
                                                    type="button"
                                                    wire:click.stop="openEditPanel({{ $item->id }})"
                                                    class="px-3 py-1.5 bg-white hover:bg-slate-50 text-blue-700 text-xs font-semibold rounded-lg border border-blue-200 shadow-2xs transition cursor-pointer"
                                                >
                                                    Ubah
                                                </button>
                                            @endif

                                            @if ($canCancel && $item->canBeCancelled())
                                                <button
                                                    type="button"
                                                    wire:click.stop="openCancelModal({{ $item->id }})"
                                                    class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition cursor-pointer"
                                                >
                                                    Batalkan
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-slate-400">
                                        <p class="text-sm font-semibold text-slate-700">Belum ada data rujukan medis</p>
                                        <p class="text-xs text-slate-400 mt-1">
                                            @if ($canCreate)
                                                Pilih tombol Buat Rujukan untuk merujuk pasien dari catatan pemeriksaan.
                                            @else
                                                Belum ada rujukan yang tercatat pada filter saat ini.
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3.5 border-t border-slate-100 bg-white">
                    <p class="text-xs text-slate-400">
                        Nakes dapat membuat, memperbarui, dan membatalkan rujukan berstatus Diajukan. Koordinator posko dapat memperbarui status rujukan poskonya.
                    </p>
                </div>

                @if ($referrals->hasPages())
                    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                        {{ $referrals->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Contextual Side Panel (Edit / Detail+Riwayat / Create) -->
        <div class="lg:col-span-5 xl:col-span-5">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6">

                {{-- MODE 1: UBAH RUJUKAN & UPDATE STATUS (Sesuai Mockup Panel Kanan) --}}
                @if ($panelMode === 'edit' && $selectedReferral)
                    <div class="flex items-center justify-between mb-4 gap-2">
                        <h2 class="text-base font-bold text-slate-900 truncate">
                            Ubah rujukan: {{ $selectedReferral->healthCheck?->refugee?->head_name }}
                        </h2>
                        <button
                            type="button"
                            wire:click="openDetailPanel({{ $selectedReferral->id }})"
                            class="text-xs font-semibold text-blue-600 hover:text-blue-700 shrink-0 cursor-pointer"
                        >
                            Detail &amp; Riwayat
                        </button>
                    </div>

                    <form wire:submit.prevent="updateReferral" class="space-y-4">
                        <!-- Pemeriksaan Asal (Terkunci) -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Pemeriksaan asal</label>
                            <div class="w-full py-2.5 px-3.5 text-xs sm:text-sm bg-slate-50 text-slate-800 font-medium rounded-xl border border-slate-200 flex items-center justify-between gap-2">
                                <span class="truncate">
                                    {{ $selectedReferral->healthCheck?->checked_at?->translatedFormat('d M H.i') }} · Triase {{ ucfirst((string) $selectedReferral->healthCheck?->triage) }}
                                </span>
                                @if ($selectedReferral->healthCheck?->triage === 'merah')
                                    <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[11px] font-semibold shrink-0">Merah</span>
                                @elseif ($selectedReferral->healthCheck?->triage === 'kuning')
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-semibold shrink-0">Kuning</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-semibold shrink-0">Hijau</span>
                                @endif
                            </div>
                        </div>

                        @php
                            $canEditDetails = $authUser->isNakes() && $selectedReferral->isDiajukan();
                        @endphp

                        <!-- Fasilitas Kesehatan Tujuan -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Fasilitas kesehatan tujuan</label>
                            <input
                                type="text"
                                wire:model="facility_name"
                                @disabled(! $canEditDetails)
                                class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 transition {{ $canEditDetails ? 'bg-slate-50/70 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent' : 'bg-slate-50 text-slate-600 cursor-not-allowed' }}"
                            />
                            @error('facility_name') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Alasan Rujukan -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Alasan rujukan</label>
                            <input
                                type="text"
                                wire:model="reason"
                                @disabled(! $canEditDetails)
                                class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 transition {{ $canEditDetails ? 'bg-slate-50/70 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent' : 'bg-slate-50 text-slate-600 cursor-not-allowed' }}"
                            />
                            @error('reason') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Kondisi Pasien -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Kondisi pasien</label>
                            <input
                                type="text"
                                wire:model="patient_condition"
                                @disabled(! $canEditDetails)
                                class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 transition {{ $canEditDetails ? 'bg-slate-50/70 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent' : 'bg-slate-50 text-slate-600 cursor-not-allowed' }}"
                            />
                            @error('patient_condition') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Transportasi & Status (2 Kolom sesuai mockup) -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Transportasi</label>
                                <select
                                    wire:model.boolean="transport_needed"
                                    @disabled(! $canEditDetails)
                                    class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 transition {{ $canEditDetails ? 'bg-slate-50/70 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent' : 'bg-slate-50 text-slate-600 cursor-not-allowed' }}"
                                >
                                    <option value="1">Butuh transportasi</option>
                                    <option value="0">Tanpa transportasi</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Status</label>
                                <select
                                    wire:model.live="status"
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                >
                                    @foreach ($selectedReferral->allowedNextStatuses() as $allowedStatus)
                                        <option value="{{ $allowedStatus }}">
                                            {{ \App\Models\MedicalReferral::statusLabel($allowedStatus) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Hasil Akhir (Wajib saat Status = Selesai) -->
                        @if ($status === 'selesai')
                            <div class="p-3.5 rounded-xl bg-emerald-50/80 border border-emerald-200/80 space-y-2">
                                <label class="block text-xs font-semibold text-emerald-900">Hasil akhir (wajib)</label>
                                <select
                                    wire:model="outcome"
                                    required
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-white text-slate-800 rounded-xl border border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition"
                                >
                                    <option value="">-- Pilih hasil akhir --</option>
                                    <option value="kembali_ke_posko">Kembali ke posko</option>
                                    <option value="dirawat">Dirawat di faskes</option>
                                </select>
                                @error('outcome') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <!-- Catatan Perkembangan -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Catatan perkembangan</label>
                            <textarea
                                rows="2"
                                wire:model="progress_note"
                                placeholder="Contoh: Berangkat 08.25 didampingi 1 relawan medis"
                                class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            ></textarea>
                            @error('progress_note') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Status Selesai mewajibkan hasil akhir: kembali ke posko atau dirawat.
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

                {{-- MODE 2: DETAIL RUJUKAN (PEMERIKSAAN ASAL & RIWAYAT STATUS - AC 2) --}}
                @elseif ($panelMode === 'detail')
                    @if ($selectedReferral)
                        @php
                            $detailBadge = match ($selectedReferral->status) {
                                'diajukan'         => 'bg-amber-100 text-amber-800',
                                'dalam_perjalanan' => 'bg-blue-100 text-blue-700',
                                'diterima_faskes'  => 'bg-indigo-100 text-indigo-700',
                                'selesai'          => 'bg-emerald-100 text-emerald-800',
                                default            => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <div class="space-y-5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h2 class="text-base font-bold text-slate-900 truncate">
                                        Detail: {{ $selectedReferral->healthCheck?->refugee?->head_name ?? '-' }}
                                    </h2>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $selectedReferral->facility_name }} · {{ $selectedReferral->transport_needed ? 'Butuh transportasi' : 'Tanpa transportasi' }}
                                    </p>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold shrink-0 {{ $detailBadge }}">
                                    {{ $selectedReferral->status_label }}
                                </span>
                            </div>

                            <!-- Info Rujukan -->
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                                <p><span class="font-semibold text-slate-700">Alasan rujukan:</span> <span class="text-slate-600">{{ $selectedReferral->reason }}</span></p>
                                <p><span class="font-semibold text-slate-700">Kondisi pasien:</span> <span class="text-slate-600">{{ $selectedReferral->patient_condition }}</span></p>
                                <p><span class="font-semibold text-slate-700">Diajukan oleh:</span> <span class="text-slate-600">{{ $selectedReferral->creator?->name ?? '-' }}</span></p>
                                @if ($selectedReferral->outcome)
                                    <p><span class="font-semibold text-emerald-800">Hasil akhir:</span> <span class="font-semibold text-emerald-700">{{ $selectedReferral->outcome_label }}</span></p>
                                @endif
                                @if ($selectedReferral->cancel_reason)
                                    <p><span class="font-semibold text-red-700">Alasan pembatalan:</span> <span class="text-red-700">{{ $selectedReferral->cancel_reason }}</span></p>
                                @endif
                            </div>

                            <!-- Pemeriksaan Asal -->
                            @if ($selectedReferral->healthCheck)
                                @php
                                    $hc = $selectedReferral->healthCheck;
                                    $hcTriage = match ($hc->triage) {
                                        'merah'  => 'bg-red-100 text-red-700',
                                        'kuning' => 'bg-amber-100 text-amber-800',
                                        default  => 'bg-emerald-100 text-emerald-800',
                                    };
                                @endphp
                                <div class="space-y-2">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Pemeriksaan Asal
                                    </h3>
                                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-slate-800">
                                                {{ $hc->checked_at?->format('d/m/Y H.i') }} · {{ $hc->refugee?->shelter?->name ?? '-' }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full font-semibold {{ $hcTriage }}">
                                                {{ ucfirst($hc->triage) }}
                                            </span>
                                        </div>
                                        <p class="text-slate-700 font-medium">{{ $hc->complaint }}</p>
                                        <p class="text-slate-500 font-mono text-[11px]">
                                            Suhu {{ $hc->temperature }}°C · TD {{ $hc->blood_pressure }} · Nadi {{ $hc->pulse }}x/menit
                                        </p>
                                        <p class="text-slate-400 text-[11px]">Pemeriksa: {{ $hc->nakes?->name ?? '-' }}</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Riwayat Status & Catatan Perkembangan -->
                            <div class="space-y-2.5">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Riwayat Status
                                </h3>
                                <ol class="relative border-l border-slate-200 ml-2 space-y-3">
                                    @foreach ($statusHistory as $entry)
                                        <li class="pl-4 relative text-xs">
                                            <span class="absolute -left-[5px] top-1.5 w-2.5 h-2.5 rounded-full bg-blue-600 ring-2 ring-white"></span>
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="font-semibold text-slate-800">
                                                    {{ $entry['status_label'] ?? 'Pembaruan' }}
                                                </span>
                                                <span class="text-[11px] font-mono text-slate-400">
                                                    {{ $entry['created_at_formatted'] ?? '-' }}
                                                </span>
                                            </div>
                                            @if (! empty($entry['note']))
                                                <p class="text-slate-600 mt-0.5">{{ $entry['note'] }}</p>
                                            @endif
                                            @if (! empty($entry['outcome_label']))
                                                <p class="text-emerald-700 font-medium mt-0.5">Hasil akhir: {{ $entry['outcome_label'] }}</p>
                                            @endif
                                            <p class="text-[11px] text-slate-400 mt-0.5">
                                                Oleh {{ $entry['actor_name'] }}{{ ! empty($entry['actor_role']) ? ' · '.$entry['actor_role'] : '' }}
                                            </p>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>

                            <!-- Quick Actions -->
                            @if ($canUpdate && $selectedReferral->canUpdateStatus())
                                <div class="pt-1 flex items-center gap-2">
                                    <button
                                        type="button"
                                        wire:click="openEditPanel({{ $selectedReferral->id }})"
                                        class="flex-1 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition cursor-pointer"
                                    >
                                        Perbarui Status
                                    </button>
                                    @if ($canCancel && $selectedReferral->canBeCancelled())
                                        <button
                                            type="button"
                                            wire:click="openCancelModal({{ $selectedReferral->id }})"
                                            class="py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition cursor-pointer"
                                        >
                                            Batalkan
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="py-10 text-center space-y-1.5">
                            <p class="text-sm font-semibold text-slate-700">Pilih rujukan medis</p>
                            <p class="text-xs text-slate-400">Klik nama pasien atau tombol Detail/Ubah pada tabel untuk melihat pemeriksaan asal dan riwayat status.</p>
                        </div>
                    @endif

                {{-- MODE 3 (DEFAULT NAKES): BUAT RUJUKAN BARU (AC 1) --}}
                @else
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-slate-900">
                            Buat Rujukan Medis
                        </h2>
                    </div>

                    <form wire:submit.prevent="storeReferral" class="space-y-4">
                        <!-- Pemeriksaan Asal (Searchable Combobox, prioritas Triase Merah) -->
                        @php
                            $checkOptions = $selectableChecks->map(fn ($c) => [
                                'id' => (string) $c->id,
                                'refugee' => $c->refugee?->head_name ?? '-',
                                'shelter' => $c->refugee?->shelter?->name ?? '-',
                                'triage' => $c->triage,
                                'complaint' => \Illuminate\Support\Str::limit($c->complaint, 48),
                                'checked_at' => $c->checked_at?->format('d/m H.i') ?? '',
                                'label' => ($c->refugee?->head_name ?? '-') . ' · Triase ' . ucfirst($c->triage) . ' (' . ($c->checked_at?->format('d/m H.i') ?? '') . ')',
                            ])->values()->all();
                        @endphp
                        <div
                            x-data="{
                                open: false,
                                search: '',
                                options: @js($checkOptions),
                                get selectedId() { return String($wire.health_check_id ?? ''); },
                                get selectedOption() {
                                    return this.options.find(o => o.id === this.selectedId) || null;
                                },
                                get filteredOptions() {
                                    const q = this.search.trim().toLowerCase();
                                    if (!q) return this.options;
                                    return this.options.filter(o =>
                                        o.refugee.toLowerCase().includes(q) ||
                                        o.complaint.toLowerCase().includes(q) ||
                                        o.triage.toLowerCase().includes(q)
                                    );
                                },
                                pick(id) {
                                    $wire.call('selectHealthCheck', parseInt(id));
                                    this.search = '';
                                    this.open = false;
                                },
                                clear() {
                                    $wire.call('clearSelectedHealthCheck');
                                    this.search = '';
                                    this.open = false;
                                }
                            }"
                            @click.outside="open = false; search = ''"
                            @keydown.escape.window="open = false; search = ''"
                            class="relative"
                        >
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Pemeriksaan asal</label>
                            <div class="relative">
                                <input
                                    type="text"
                                    x-model="search"
                                    @focus="open = true"
                                    @input="open = true"
                                    :placeholder="selectedOption ? selectedOption.label : 'Cari pasien atau pemeriksaan (Triase Merah di atas)...'"
                                    :class="selectedOption && !open ? 'placeholder:text-slate-800 placeholder:font-medium' : 'placeholder:text-slate-400'"
                                    class="w-full py-2 pl-3 pr-9 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2.5">
                                    <template x-if="selectedId !== ''">
                                        <button type="button" @click.stop="clear()" class="p-1 text-slate-400 hover:text-slate-600 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </template>
                                    <template x-if="selectedId === ''">
                                        <button type="button" @click="open = !open" class="p-1 text-slate-400 hover:text-slate-600 cursor-pointer">
                                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                class="absolute z-40 mt-1.5 w-full bg-white rounded-xl border border-slate-200 shadow-lg max-h-64 overflow-y-auto divide-y divide-slate-100"
                            >
                                <template x-for="item in filteredOptions" :key="item.id">
                                    <button
                                        type="button"
                                        @click="pick(item.id)"
                                        class="w-full px-3.5 py-2.5 text-left hover:bg-slate-50 transition flex items-start justify-between gap-2 cursor-pointer"
                                        :class="selectedId === item.id ? 'bg-blue-50/70' : ''"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <span class="text-xs sm:text-sm font-semibold text-slate-800 block truncate" x-text="item.refugee"></span>
                                            <span class="text-[11px] text-slate-500 block truncate" x-text="item.complaint"></span>
                                            <span class="text-[10px] text-slate-400 block truncate" x-text="item.shelter + ' · ' + item.checked_at"></span>
                                        </div>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0 uppercase"
                                            :class="item.triage === 'merah' ? 'bg-red-100 text-red-700' : (item.triage === 'kuning' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800')"
                                            x-text="item.triage"
                                        ></span>
                                    </button>
                                </template>
                                <div x-show="filteredOptions.length === 0" class="px-3.5 py-4 text-center text-xs text-slate-400">
                                    Tidak ada pemeriksaan tanpa rujukan aktif.
                                </div>
                            </div>
                            @error('health_check_id') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Fasilitas Kesehatan Tujuan -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Fasilitas kesehatan tujuan</label>
                            <input
                                type="text"
                                wire:model="facility_name"
                                required
                                placeholder="Contoh: RSUD Al-Ihsan, Baleendah"
                                class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            />
                            @error('facility_name') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Alasan Rujukan -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Alasan rujukan</label>
                            <input
                                type="text"
                                wire:model="reason"
                                required
                                placeholder="Contoh: Sesak berat, saturasi oksigen rendah"
                                class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            />
                            @error('reason') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Kondisi Pasien -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Kondisi pasien</label>
                            <textarea
                                rows="2"
                                wire:model="patient_condition"
                                required
                                placeholder="Contoh: Pasien sadar, terpasang oksigen nasal kanul 4 lpm"
                                class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            ></textarea>
                            @error('patient_condition') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Kebutuhan Transportasi -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Kebutuhan transportasi</label>
                            <select
                                wire:model.boolean="transport_needed"
                                class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            >
                                <option value="1">Ya — Membutuhkan transportasi / ambulans</option>
                                <option value="0">Tidak — Tanpa kebutuhan transportasi khusus</option>
                            </select>
                            @error('transport_needed') <span class="text-[11px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="p-3 rounded-xl bg-blue-50/80 border border-blue-200/80 text-xs text-blue-800 leading-relaxed">
                            Status awal rujukan otomatis <strong>Diajukan</strong> dan koordinator posko terkait akan langsung menerima notifikasi.
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <button
                                type="button"
                                wire:click="closeFormPanel"
                                class="w-full py-2.5 px-4 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 transition cursor-pointer"
                            >
                                Reset
                            </button>
                            <button
                                type="submit"
                                class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition cursor-pointer"
                            >
                                Ajukan Rujukan
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Pembatalan Rujukan (Sesuai Mockup Bagian Bawah) -->
    @if ($showCancelModal && $selectedReferral)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" wire:click="closeCancelModal" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6">
                    <h3 class="text-base font-bold text-slate-900">
                        Batalkan rujukan "{{ $selectedReferral->healthCheck?->refugee?->head_name }}"?
                    </h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Hanya rujukan berstatus Diajukan yang dapat dibatalkan. Rujukan yang sudah berjalan tidak dapat dihapus.
                    </p>

                    @if (! $selectedReferral->canBeCancelled())
                        <div class="mt-4 p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700">
                            Rujukan ini berstatus <strong>{{ $selectedReferral->status_label }}</strong> dan tidak dapat dibatalkan.
                        </div>
                    @endif

                    @error('cancel')
                        <div class="mt-3 p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alasan (wajib)</label>
                            <select
                                wire:model.live="cancel_reason"
                                class="w-full py-2.5 px-3 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            >
                                @foreach (\App\Livewire\Admin\MedicalReferralManagement::CANCEL_REASON_PRESETS as $preset)
                                    <option value="{{ $preset }}">{{ $preset }}</option>
                                @endforeach
                                <option value="lainnya">Alasan lainnya...</option>
                            </select>
                        </div>

                        @if ($cancel_reason === 'lainnya')
                            <div>
                                <input
                                    type="text"
                                    wire:model="custom_cancel_reason"
                                    placeholder="Tuliskan alasan pembatalan..."
                                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50/70 focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                                />
                            </div>
                        @endif

                        @error('cancel_reason') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-6 flex items-center justify-end space-x-3">
                        <button
                            type="button"
                            wire:click="closeCancelModal"
                            class="px-4 py-2 bg-white text-slate-700 hover:bg-slate-100 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 transition cursor-pointer"
                        >
                            Kembali
                        </button>
                        <button
                            type="button"
                            wire:click="confirmCancel"
                            @disabled(! $selectedReferral->canBeCancelled())
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-300 disabled:cursor-not-allowed text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition cursor-pointer"
                        >
                            Batalkan Rujukan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
