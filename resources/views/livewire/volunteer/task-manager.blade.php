{{-- Halaman Tugas Relawan (PBI-20) --}}
<div class="space-y-5">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-[11px] text-slate-400" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="transition hover:text-slate-600">Beranda</a>
        <span>/</span>
        <span class="font-medium text-slate-600">Tugas Relawan</span>
    </nav>

    {{-- Judul halaman --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900">Tugas Relawan</h1>
            <p class="mt-0.5 text-xs text-slate-500">
                PBI-20 &middot; Buat tugas, dapatkan rekomendasi relawan yang cocok, dan kirim penawaran
            </p>
        </div>

        @if ($this->isCoordinator())
            <button type="button" wire:click="openCreate"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-[4px] bg-blue-600 px-3.5 py-2 text-xs font-medium text-white transition hover:bg-blue-700">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Tugas
            </button>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Kolom kiri: daftar tugas + rekomendasi --}}
        <div class="space-y-4 lg:col-span-2">

            {{-- Filter status --}}
            <div class="flex flex-wrap items-center gap-1.5">
                @php
                    $filters = [
                        'semua' => 'Semua',
                        'terbuka' => 'Terbuka',
                        'berjalan' => 'Berjalan',
                        'selesai' => 'Selesai',
                    ];
                @endphp

                @foreach ($filters as $key => $label)
                    <button type="button" wire:click="setFilter('{{ $key }}')"
                            class="inline-flex items-center gap-1.5 rounded-[4px] border px-2.5 py-1 text-[11px] font-medium transition {{ $statusFilter === $key ? 'border-blue-300 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50' }}">
                        {{ $label }}
                        <span class="rounded-[3px] px-1 text-[10px] {{ $statusFilter === $key ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $counts[$key] ?? 0 }}
                        </span>
                    </button>
                @endforeach
            </div>

            {{-- Daftar tugas --}}
            <div class="overflow-hidden rounded-[4px] border border-slate-200 bg-white">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-2.5">
                    <p class="text-sm font-semibold text-slate-900">
                        {{ $this->isCoordinator() ? 'Daftar tugas' : 'Tugas saya' }}
                    </p>
                    <span class="text-[10px] text-slate-400">{{ $tasks->count() }} tugas</span>
                </div>

                @if ($tasks->isEmpty())
                    <div class="px-4 py-10 text-center">
                        <p class="text-xs text-slate-400">Belum ada tugas pada filter ini.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($tasks as $task)
                            @php
                                $statusMap = [
                                    'terbuka' => ['Terbuka', 'bg-amber-50 text-amber-700'],
                                    'berjalan' => ['Berjalan', 'bg-blue-50 text-blue-700'],
                                    'selesai' => ['Selesai', 'bg-emerald-50 text-emerald-700'],
                                    'dibatalkan' => ['Dibatalkan', 'bg-rose-50 text-rose-700'],
                                ];
                                [$statusLabel, $statusClass] = $statusMap[$task->status] ?? [ucfirst($task->status), 'bg-slate-100 text-slate-600'];
                                $mine = $myAssignments[$task->id] ?? null;
                            @endphp

                            <div class="px-4 py-3 transition hover:bg-slate-50/60">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <p class="text-xs font-semibold leading-snug text-slate-900">{{ $task->title }}</p>
                                            <span class="rounded-[4px] px-1.5 py-0.5 text-[10px] font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                                        </div>

                                        <p class="mt-1 text-[10px] text-slate-400">
                                            {{ $task->skill_name }}
                                            &middot; {{ (int) $task->taken }}/{{ $task->quota }} relawan
                                        </p>
                                        <p class="mt-0.5 text-[10px] text-slate-400">
                                            {{ \Illuminate\Support\Carbon::parse($task->start_at)->translatedFormat('d M Y H:i') }}
                                            &ndash; {{ \Illuminate\Support\Carbon::parse($task->end_at)->translatedFormat('d M Y H:i') }}
                                        </p>

                                        @if ($mine && $task->quota > 0)
                                            @php
                                                $offerMap = [
                                                    'ditawarkan' => ['Penawaran masuk', 'bg-amber-50 text-amber-700'],
                                                    'diterima' => ['Diterima', 'bg-blue-50 text-blue-700'],
                                                    'selesai' => ['Selesai', 'bg-emerald-50 text-emerald-700'],
                                                    'ditolak' => ['Ditolak', 'bg-rose-50 text-rose-700'],
                                                ];
                                                [$offerLabel, $offerClass] = $offerMap[$mine->status] ?? [ucfirst($mine->status), 'bg-slate-100 text-slate-600'];
                                            @endphp
                                            <span class="mt-1.5 inline-flex items-center rounded-[4px] px-1.5 py-0.5 text-[10px] font-medium {{ $offerClass }}">
                                                Status penawaran saya: {{ $offerLabel }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex shrink-0 items-center gap-1.5">
                                        @if ($mine && $mine->status === 'ditawarkan')
                                            <button type="button" wire:click="acceptOffer({{ $mine->id }})"
                                                    class="rounded-[4px] bg-blue-600 px-2.5 py-1 text-[10px] font-medium text-white transition hover:bg-blue-700">
                                                Terima
                                            </button>
                                            <button type="button" wire:click="declineOffer({{ $mine->id }})"
                                                    class="rounded-[4px] border border-slate-300 bg-white px-2.5 py-1 text-[10px] font-medium text-slate-700 transition hover:bg-slate-50">
                                                Tolak
                                            </button>
                                        @endif

                                        <button type="button" wire:click="openDetail({{ $task->id }})"
                                                class="rounded-[4px] border border-slate-300 bg-white px-2.5 py-1 text-[10px] font-medium text-slate-700 transition hover:bg-slate-50">
                                            Detail
                                        </button>

                                        @if ($this->isCoordinator() && in_array($task->status, ['terbuka', 'berjalan'], true))
                                            <button type="button" wire:click="openEdit({{ $task->id }})"
                                                    class="rounded-[4px] border border-slate-300 bg-white px-2.5 py-1 text-[10px] font-medium text-slate-700 transition hover:bg-slate-50">
                                                Ubah
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Rekomendasi relawan (koordinator) --}}
            @if ($this->isCoordinator() && $showForm)
                <div class="overflow-hidden rounded-[4px] border border-slate-200 bg-white">
                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-2.5">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Rekomendasi relawan</p>
                            <p class="mt-0.5 text-[10px] text-slate-400">
                                @if ($skillName)
                                    Status Siap &middot; keahlian {{ $skillName }} &middot; urut jarak lalu tugas aktif
                                @else
                                    Pilih keahlian dibutuhkan untuk melihat rekomendasi
                                @endif
                            </p>
                        </div>
                        <span class="text-[10px] text-slate-400">{{ count($recommendations) }} relawan</span>
                    </div>

                    @if (empty($recommendations))
                        <div class="px-4 py-8 text-center">
                            <p class="text-xs text-slate-400">
                                {{ $skillName ? 'Belum ada relawan yang cocok dan bebas jadwal bentrok.' : 'Rekomendasi muncul setelah keahlian dan jadwal diisi.' }}
                            </p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach ($recommendations as $index => $row)
                                <div class="flex items-center justify-between gap-3 px-4 py-2.5">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-semibold text-slate-600">
                                            {{ $index + 1 }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-medium text-slate-800">{{ $row['name'] }}</p>
                                            <p class="text-[10px] text-slate-400">
                                                {{ $row['distance'] !== null ? $row['distance'].' km dari lokasi' : 'Jarak belum diketahui' }}
                                                &middot; {{ $row['active'] }} tugas aktif
                                            </p>
                                        </div>
                                    </div>

                                    <button type="button" wire:click="toggleProfile({{ $row['profile_id'] }})"
                                            class="shrink-0 rounded-[4px] border px-2.5 py-1 text-[10px] font-medium transition {{ $row['selected'] ? 'border-blue-600 bg-blue-600 text-white hover:bg-blue-700' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}">
                                        {{ $row['selected'] ? 'Dipilih' : 'Pilih' }}
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Kolom kanan: panel buat/ubah tugas atau detail --}}
        <div class="lg:col-span-1">
            <div class="lg:sticky lg:top-20">
                @if ($detailId)
                    {{-- Detail tugas --}}
                    <div class="overflow-hidden rounded-[4px] border border-slate-200 bg-white">
                        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-2.5">
                            <p class="text-sm font-semibold text-slate-900">Detail tugas</p>
                            <button type="button" wire:click="closePanel" class="rounded p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup detail">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        @if ($detailTask)
                            <div class="space-y-3 px-4 py-3.5">
                                <div>
                                    <p class="text-xs font-semibold text-slate-900">{{ $detailTask->title }}</p>
                                    <p class="mt-1 text-[10px] leading-relaxed text-slate-500">{{ $detailTask->description }}</p>
                                </div>

                                <dl class="space-y-1.5 text-[10px]">
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-400">Keahlian</dt>
                                        <dd class="text-right font-medium text-slate-700">{{ $detailTask->skill?->name ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-400">Kuota</dt>
                                        <dd class="text-right font-medium text-slate-700">
                                            {{ $detailTask->assignments()->whereIn('status', ['ditawarkan', 'diterima', 'selesai'])->count() }}/{{ $detailTask->quota }} terisi
                                        </dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-400">Jadwal</dt>
                                        <dd class="text-right font-medium text-slate-700">
                                            {{ $detailTask->start_at?->translatedFormat('d M Y H:i') }}
                                            &ndash; {{ $detailTask->end_at?->translatedFormat('d M Y H:i') }}
                                        </dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-400">Lokasi</dt>
                                        <dd class="text-right font-medium text-slate-700">{{ $detailTask->lat }}, {{ $detailTask->lng }}</dd>
                                    </div>
                                </dl>

                                <div class="rounded-[4px] border border-slate-200">
                                    <p class="border-b border-slate-200 px-3 py-2 text-[11px] font-semibold text-slate-800">Status penawaran relawan</p>
                                    @if (empty($offers))
                                        <p class="px-3 py-4 text-center text-[10px] text-slate-400">Belum ada penawaran terkirim.</p>
                                    @else
                                        <div class="divide-y divide-slate-100">
                                            @foreach ($offers as $offer)
                                                @php
                                                    $offerMap = [
                                                        'ditawarkan' => ['Ditawarkan', 'bg-amber-50 text-amber-700'],
                                                        'diterima' => ['Diterima', 'bg-blue-50 text-blue-700'],
                                                        'selesai' => ['Selesai', 'bg-emerald-50 text-emerald-700'],
                                                        'ditolak' => ['Ditolak', 'bg-rose-50 text-rose-700'],
                                                    ];
                                                    [$offerLabel, $offerClass] = $offerMap[$offer->status] ?? [ucfirst($offer->status), 'bg-slate-100 text-slate-600'];
                                                @endphp
                                                <div class="flex items-center justify-between gap-2 px-3 py-2">
                                                    <p class="truncate text-[11px] text-slate-700">{{ $offer->name }}</p>
                                                    <span class="shrink-0 rounded-[4px] px-1.5 py-0.5 text-[10px] font-medium {{ $offerClass }}">{{ $offerLabel }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @if ($this->isCoordinator() && in_array($detailTask->status, ['terbuka', 'berjalan'], true))
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" wire:click="openEdit({{ $detailTask->id }})"
                                                class="rounded-[4px] bg-blue-600 py-2 text-[11px] font-medium text-white transition hover:bg-blue-700">
                                            Ubah Tugas
                                        </button>
                                        <button type="button" wire:click="cancelTask({{ $detailTask->id }})"
                                                class="rounded-[4px] border border-slate-300 bg-white py-2 text-[11px] font-medium text-rose-600 transition hover:bg-rose-50">
                                            Batalkan
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif ($showForm)
                    {{-- Form buat/ubah tugas --}}
                    <div class="overflow-hidden rounded-[4px] border border-slate-200 bg-white">
                        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-2.5">
                            <p class="text-sm font-semibold text-slate-900">{{ $editingId ? 'Ubah tugas' : 'Buat tugas' }}</p>
                            <button type="button" wire:click="closePanel" class="rounded p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup form">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form wire:submit="{{ $editingId ? 'saveChanges' : 'sendOffers' }}" class="space-y-3 px-4 py-3.5">

                            @unless ($editingId)
                                <div>
                                    <label class="mb-1 block text-[11px] font-semibold text-slate-700">Buat dari SOS (opsional)</label>
                                    <select wire:model.live="sosRequestId"
                                            class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                        <option value="">Tidak terkait SOS</option>
                                        @foreach ($sosOptions as $sos)
                                            <option value="{{ $sos->id }}">
                                                #{{ $sos->id }} &middot; {{ ucfirst($sos->help_type) }} &middot; {{ $sos->requester_name }} ({{ $sos->people_count }} org)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('sosRequestId')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                                </div>
                            @endunless

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-slate-700">Judul tugas</label>
                                <input type="text" wire:model="title" placeholder="Contoh: Distribusi logistik Posko Al-Ikhlas"
                                       class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                @error('title')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-slate-700">Deskripsi</label>
                                <textarea wire:model="description" rows="3" placeholder="Rincian pekerjaan, kebutuhan alat, dan instruksi"
                                          class="w-full resize-none rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"></textarea>
                                @error('description')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="mb-1 block text-[11px] font-semibold text-slate-700">Keahlian dibutuhkan</label>
                                    <select wire:model.live="skillId"
                                            class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                        <option value="">Pilih keahlian</option>
                                        @foreach ($skills as $skill)
                                            <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('skillId')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-[11px] font-semibold text-slate-700">Kuota relawan</label>
                                    <input type="number" wire:model.live="quota" min="1" max="50"
                                           class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    @error('quota')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="mb-1 block text-[11px] font-semibold text-slate-700">Mulai</label>
                                    <input type="datetime-local" wire:model.live="startAt"
                                           class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    @error('startAt')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-[11px] font-semibold text-slate-700">Selesai</label>
                                    <input type="datetime-local" wire:model.live="endAt"
                                           class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    @error('endAt')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-slate-700">Lokasi tugas</label>
                                <select wire:model.live="shelterId"
                                        class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <option value="">Pilih posko (atau isi koordinat manual)</option>
                                    @foreach ($shelters as $shelter)
                                        <option value="{{ $shelter->id }}">{{ $shelter->name }}</option>
                                    @endforeach
                                </select>
                                @error('shelterId')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="mb-1 block text-[11px] font-semibold text-slate-700">Latitude</label>
                                    <input type="text" wire:model.live.debounce.500ms="lat" placeholder="-6.9961000"
                                           class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    @error('lat')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-semibold text-slate-700">Longitude</label>
                                    <input type="text" wire:model.live.debounce.500ms="lng" placeholder="107.6238000"
                                           class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    @error('lng')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            {{-- Peta lokasi (placeholder) --}}
                            <div class="flex h-24 items-center justify-center rounded-[4px] border border-dashed border-slate-200 bg-slate-50">
                                <p class="text-[10px] text-slate-400">
                                    @if (is_numeric($lat) && is_numeric($lng))
                                        Lokasi {{ $lat }}, {{ $lng }}
                                    @else
                                        Pilih posko atau isi koordinat untuk menandai lokasi
                                    @endif
                                </p>
                            </div>

                            @unless ($editingId)
                                <p class="text-[10px] text-slate-400">
                                    Dipilih {{ count($selectedProfiles) }} dari kuota {{ $quota }} relawan.
                                    Penawaran dikirim maksimal sesuai kuota.
                                </p>
                            @endunless

                            <div class="flex gap-2 border-t border-slate-200 pt-3">
                                <button type="button" wire:click="closePanel"
                                        class="flex-1 rounded-[4px] border border-slate-300 bg-white py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-50">
                                    Batal
                                </button>
                                <button type="submit" wire:loading.attr="disabled" wire:target="{{ $editingId ? 'saveChanges' : 'sendOffers' }}"
                                        class="flex-1 rounded-[4px] bg-blue-600 py-2 text-xs font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">
                                    @if ($editingId)
                                        <span wire:loading.remove wire:target="saveChanges">Simpan Perubahan</span>
                                        <span wire:loading wire:target="saveChanges">Menyimpan...</span>
                                    @else
                                        <span wire:loading.remove wire:target="sendOffers">Kirim Penawaran ({{ count($selectedProfiles) }})</span>
                                        <span wire:loading wire:target="sendOffers">Mengirim...</span>
                                    @endif
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    {{-- Panel kosong --}}
                    <div class="rounded-[4px] border border-dashed border-slate-200 bg-white px-4 py-8 text-center">
                        <p class="text-xs font-medium text-slate-600">
                            {{ $this->isCoordinator() ? 'Buat tugas baru' : 'Pilih tugas untuk melihat detail' }}
                        </p>
                        <p class="mt-1 text-[10px] leading-relaxed text-slate-400">
                            @if ($this->isCoordinator())
                                Isi judul, deskripsi, lokasi, keahlian, kuota, dan jadwal. Sistem menyarankan relawan berstatus Siap.
                            @else
                                Tekan tombol Detail pada daftar tugas untuk melihat jadwal dan status penawaran Anda.
                            @endif
                        </p>
                        @if ($this->isCoordinator())
                            <button type="button" wire:click="openCreate"
                                    class="mt-3 rounded-[4px] bg-blue-600 px-3.5 py-1.5 text-[11px] font-medium text-white transition hover:bg-blue-700">
                                Buat Tugas
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if ($notification)
        <div class="fixed left-4 right-4 top-4 z-[80] mx-auto max-w-lg rounded-lg px-4 py-3 text-sm text-white shadow-xl {{ $notificationType === 'success' ? 'bg-emerald-600' : 'bg-rose-600' }}">
            <div class="flex items-start justify-between gap-3">
                <span>{{ $notification }}</span>
                <button type="button" wire:click="dismissNotification" class="shrink-0 text-white/80 hover:text-white" aria-label="Tutup notifikasi">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif
</div>
