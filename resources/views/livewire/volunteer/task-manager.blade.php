{{-- Halaman Tugas Relawan (PBI-20) --}}
<div class="space-y-4">

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
                Buat tugas, dapatkan rekomendasi relawan yang cocok, dan kirim penawaran
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

        {{-- Kolom kiri --}}
        <div class="space-y-4 lg:col-span-2">

            {{-- Daftar tugas --}}
            <div class="overflow-hidden rounded-[6px] border border-slate-200 bg-white">
                <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3">
                    <p class="text-sm font-semibold text-slate-900">
                        {{ $this->isCoordinator() ? 'Daftar tugas' : 'Tugas saya' }}
                    </p>

                    <div class="flex items-center gap-1">
                        @foreach (['semua' => 'Semua', 'terbuka' => 'Terbuka', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai'] as $key => $label)
                            <button type="button" wire:click="setFilter('{{ $key }}')"
                                    class="rounded-[4px] px-2 py-0.5 text-[10px] font-medium transition {{ $statusFilter === $key ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-200' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600' }}">
                                {{ $label }} {{ $counts[$key] ?? 0 }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            <th class="px-4 py-2 font-medium">Tugas</th>
                            <th class="w-16 px-2 py-2 font-medium">Kuota</th>
                            <th class="w-24 px-2 py-2 font-medium">Status</th>
                            <th class="w-40 px-4 py-2 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tasks as $task)
                            @php
                                $statusMap = [
                                    'terbuka' => ['Terbuka', 'bg-amber-100 text-amber-700'],
                                    'berjalan' => ['Berjalan', 'bg-blue-100 text-blue-700'],
                                    'selesai' => ['Selesai', 'bg-emerald-100 text-emerald-700'],
                                    'dibatalkan' => ['Dibatalkan', 'bg-rose-100 text-rose-700'],
                                ];
                                [$statusLabel, $statusClass] = $statusMap[$task->status] ?? [ucfirst($task->status), 'bg-slate-100 text-slate-600'];
                                $mine = $myAssignments[$task->id] ?? null;
                            @endphp

                            <tr class="align-middle transition hover:bg-slate-50/60">
                                <td class="px-4 py-2.5">
                                    <p class="text-[11px] font-medium leading-snug text-slate-800">
                                        {{ $task->title }}@if ($task->sos_request_id) <span class="font-normal text-slate-400">(dari SOS #{{ str_pad($task->sos_request_id, 2, '0', STR_PAD_LEFT) }})</span>@endif
                                    </p>

                                    @if ($mine)
                                        @php
                                            $offerMap = [
                                                'ditawarkan' => ['Penawaran masuk', 'bg-amber-50 text-amber-700'],
                                                'diterima' => ['Diterima', 'bg-blue-50 text-blue-700'],
                                                'selesai' => ['Selesai', 'bg-emerald-50 text-emerald-700'],
                                                'ditolak' => ['Ditolak', 'bg-rose-50 text-rose-700'],
                                            ];
                                            [$offerLabel, $offerClass] = $offerMap[$mine->status] ?? [ucfirst($mine->status), 'bg-slate-100 text-slate-600'];
                                        @endphp
                                        <span class="mt-1 inline-flex items-center rounded-[3px] px-1.5 py-0.5 text-[10px] font-medium {{ $offerClass }}">
                                            {{ $offerLabel }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-2 py-2.5 text-[11px] text-slate-500">{{ (int) $task->taken }}/{{ $task->quota }}</td>

                                <td class="px-2 py-2.5">
                                    <span class="inline-flex items-center rounded-[3px] px-1.5 py-0.5 text-[10px] font-medium {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="px-4 py-2.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if ($this->isCoordinator())
                                            @if (in_array($task->status, ['terbuka', 'berjalan'], true))
                                                <button type="button" wire:click="openEdit({{ $task->id }})"
                                                        class="rounded-[4px] border border-slate-300 bg-white px-2.5 py-1 text-[10px] font-medium text-slate-700 transition hover:bg-slate-50">
                                                    Ubah
                                                </button>
                                            @endif

                                            @if ($task->status === 'terbuka')
                                                <button type="button" wire:click="requestCancel({{ $task->id }})"
                                                        class="rounded-[4px] bg-rose-600 px-2.5 py-1 text-[10px] font-medium text-white transition hover:bg-rose-700">
                                                    Batalkan
                                                </button>
                                            @endif

                                            <button type="button" wire:click="openDetail({{ $task->id }})"
                                                    class="rounded-[4px] border border-slate-300 bg-white px-2.5 py-1 text-[10px] font-medium text-slate-700 transition hover:bg-slate-50">
                                                Detail
                                            </button>
                                        @else
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
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-[11px] text-slate-400">
                                    Belum ada tugas pada filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Rekomendasi relawan --}}
            @if ($this->isCoordinator() && $showForm)
                <div class="overflow-hidden rounded-[6px] border border-slate-200 bg-white">
                    <div class="px-4 py-3">
                        <p class="text-sm font-semibold text-slate-900">
                            Rekomendasi relawan &middot; {{ $skillName ?? 'pilih keahlian' }}
                        </p>
                    </div>

                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="bg-slate-50 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                                <th class="px-4 py-2 font-medium">Relawan</th>
                                <th class="w-20 px-2 py-2 font-medium">Jarak</th>
                                <th class="w-24 px-2 py-2 font-medium">Tugas aktif</th>
                                <th class="w-24 px-4 py-2 text-right font-medium">Pilih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recommendations as $row)
                                <tr class="{{ $row['selected'] ? 'bg-blue-50/70' : 'hover:bg-slate-50/60' }} transition">
                                    <td class="px-4 py-2.5 text-[11px] font-medium text-slate-800">{{ $row['name'] }}</td>
                                    <td class="px-2 py-2.5 text-[11px] text-slate-500">
                                        {{ $row['distance'] !== null ? number_format($row['distance'], 1, ',', '.').' km' : '-' }}
                                    </td>
                                    <td class="px-2 py-2.5 text-[11px] text-slate-500">{{ $row['active'] }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <button type="button" wire:click="toggleProfile({{ $row['profile_id'] }})"
                                                class="rounded-[4px] border px-2.5 py-1 text-[10px] font-medium transition {{ $row['selected'] ? 'border-blue-200 bg-blue-100 text-blue-700 hover:bg-blue-200' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50' }}">
                                            {{ $row['selected'] ? 'Dipilih' : 'Pilih' }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-[11px] text-slate-400">
                                        {{ $skillName ? 'Belum ada relawan berstatus Siap dengan keahlian ini yang bebas jadwal bentrok.' : 'Rekomendasi muncul setelah keahlian dan jadwal diisi.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <p class="border-t border-slate-100 px-4 py-2.5 text-[10px] leading-relaxed text-slate-400">
                        Diurutkan berdasarkan jarak domisili lalu jumlah tugas aktif; relawan dengan jadwal bentrok tidak ditampilkan.
                    </p>
                </div>
            @endif
        </div>

        {{-- Kolom kanan --}}
        <div class="lg:col-span-1">
            <div class="lg:sticky lg:top-20">
                @if ($showForm)
                    <div class="overflow-hidden rounded-[6px] border border-slate-200 bg-white">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ $editingId ? 'Ubah tugas: '.$title : 'Buat tugas' }}
                            </p>
                        </div>

                        <form wire:submit="{{ $editingId ? 'saveChanges' : 'sendOffers' }}" class="space-y-3 px-4 py-3.5">

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-slate-700">Sumber</label>
                                <select wire:model.live="sosRequestId"
                                        class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <option value="">Tanpa SOS</option>
                                    @foreach ($sosOptions as $sos)
                                        <option value="{{ $sos->id }}">
                                            SOS #{{ str_pad($sos->id, 2, '0', STR_PAD_LEFT) }} &middot; {{ $sos->requester_name }} &middot; {{ $sos->people_count }} orang
                                        </option>
                                    @endforeach
                                </select>
                                @error('sosRequestId')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-slate-700">Judul tugas</label>
                                <input type="text" wire:model="title" placeholder="Evakuasi lansia RW 07"
                                       class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] text-slate-800 placeholder:text-slate-300 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                @error('title')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-slate-700">Deskripsi</label>
                                <textarea wire:model="description" rows="3" placeholder="Bantu evakuasi 2 lansia dan 4 warga ke GOR Baleendah"
                                          class="w-full resize-none rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] leading-relaxed text-slate-800 placeholder:text-slate-300 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"></textarea>
                                @error('description')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="mb-1 block text-[11px] font-semibold text-slate-700">Keahlian</label>
                                    <select wire:model.live="skillId"
                                            class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                        <option value="">Pilih</option>
                                        @foreach ($skills as $skill)
                                            <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('skillId')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-[11px] font-semibold text-slate-700">Kuota</label>
                                    <input type="number" wire:model.live="quota" min="1" max="50"
                                           class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    @error('quota')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="mb-1 block text-[11px] font-semibold text-slate-700">Mulai</label>
                                    <input type="datetime-local" wire:model.live="startAt"
                                           class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    @error('startAt')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-[11px] font-semibold text-slate-700">Selesai</label>
                                    <input type="datetime-local" wire:model.live="endAt"
                                           class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    @error('endAt')<p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            {{-- Peta lokasi --}}
                            <div class="relative h-32 overflow-hidden rounded-[4px] border border-slate-200">
                                <svg class="h-full w-full" viewBox="0 0 300 128" preserveAspectRatio="none" aria-hidden="true">
                                    <rect width="300" height="128" fill="#f8fafc" />
                                    <rect x="0" y="0" width="96" height="46" fill="#dcfce7" />
                                    <rect x="196" y="0" width="104" height="38" fill="#dcfce7" />
                                    <rect x="0" y="84" width="70" height="44" fill="#dcfce7" />
                                    <rect x="232" y="76" width="68" height="52" fill="#dcfce7" />
                                    <path d="M-10 96 C 60 96, 90 40, 160 40 S 250 78, 310 62" fill="none" stroke="#bae6fd" stroke-width="14" />
                                    <path d="M0 62 L 300 46" stroke="#e2e8f0" stroke-width="8" />
                                    <path d="M0 62 L 300 46" stroke="#ffffff" stroke-width="4" />
                                    <path d="M150 0 L 168 128" stroke="#e2e8f0" stroke-width="7" />
                                    <path d="M150 0 L 168 128" stroke="#ffffff" stroke-width="3" />
                                    <path d="M0 112 L 300 104" stroke="#e2e8f0" stroke-width="6" />
                                    <path d="M0 112 L 300 104" stroke="#ffffff" stroke-width="2.5" />
                                    <g transform="translate(205 62)">
                                        <path d="M0 12 C 6 4, 8 1, 8 -4 A 8 8 0 1 0 -8 -4 C -8 1, -6 4, 0 12 Z" fill="#dc2626" />
                                        <circle cx="0" cy="-4" r="3" fill="#ffffff" />
                                    </g>
                                </svg>

                                <select wire:model.live="shelterId"
                                        title="Posko lokasi tugas"
                                        class="absolute right-2 top-2 max-w-[58%] rounded-[3px] border border-slate-200 bg-white/95 px-1.5 py-1 text-[10px] text-slate-600 shadow-sm focus:border-blue-400 focus:outline-none">
                                    <option value="">Pilih posko</option>
                                    @foreach ($shelters as $shelter)
                                        <option value="{{ $shelter->id }}">{{ $shelter->name }}</option>
                                    @endforeach
                                </select>

                                <div class="absolute bottom-2 left-2 flex gap-1">
                                    <input type="text" wire:model.live.debounce.500ms="lat" placeholder="Lat"
                                           aria-label="Latitude"
                                           class="w-[74px] rounded-[3px] border border-slate-200 bg-white/95 px-1.5 py-0.5 text-[10px] text-slate-600 shadow-sm focus:border-blue-400 focus:outline-none">
                                    <input type="text" wire:model.live.debounce.500ms="lng" placeholder="Lng"
                                           aria-label="Longitude"
                                           class="w-[74px] rounded-[3px] border border-slate-200 bg-white/95 px-1.5 py-0.5 text-[10px] text-slate-600 shadow-sm focus:border-blue-400 focus:outline-none">
                                </div>
                            </div>
                            @error('lat')<p class="text-[10px] text-rose-500">{{ $message }}</p>@enderror
                            @error('lng')<p class="text-[10px] text-rose-500">{{ $message }}</p>@enderror
                            @error('shelterId')<p class="text-[10px] text-rose-500">{{ $message }}</p>@enderror

                            <p class="text-[10px] text-slate-400">
                                Dipilih {{ count($selectedProfiles) }} dari kuota {{ $quota }} relawan.
                            </p>

                            <div class="flex gap-2 border-t border-slate-100 pt-3">
                                <button type="button" wire:click="closePanel"
                                        class="flex-1 rounded-[4px] border border-slate-300 bg-white py-2 text-[11px] font-medium text-slate-700 transition hover:bg-slate-50">
                                    Batal
                                </button>
                                <button type="submit" wire:loading.attr="disabled" wire:target="{{ $editingId ? 'saveChanges' : 'sendOffers' }}"
                                        class="flex-1 rounded-[4px] bg-blue-600 py-2 text-[11px] font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">
                                    @if ($editingId)
                                        <span wire:loading.remove wire:target="saveChanges">
                                            {{ count($selectedProfiles) > 0 ? 'Kirim Penawaran ('.count($selectedProfiles).')' : 'Simpan Perubahan' }}
                                        </span>
                                        <span wire:loading wire:target="saveChanges">Menyimpan...</span>
                                    @else
                                        <span wire:loading.remove wire:target="sendOffers">Kirim Penawaran ({{ count($selectedProfiles) }})</span>
                                        <span wire:loading wire:target="sendOffers">Mengirim...</span>
                                    @endif
                                </button>
                            </div>
                        </form>
                    </div>
                @elseif ($detailId)
                    <div class="overflow-hidden rounded-[6px] border border-slate-200 bg-white">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">Detail tugas</p>
                        </div>

                        @if ($detailTask)
                            <div class="space-y-3 px-4 py-3.5">
                                <div>
                                    <p class="text-[11px] font-semibold text-slate-900">{{ $detailTask->title }}</p>
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
                                    @if ($detailTask->status === 'dibatalkan' && $detailTask->cancel_reason)
                                        <div class="flex justify-between gap-3">
                                            <dt class="text-slate-400">Alasan batal</dt>
                                            <dd class="text-right font-medium text-rose-600">{{ $detailTask->cancel_reason }}</dd>
                                        </div>
                                    @endif
                                </dl>

                                <div class="overflow-hidden rounded-[4px] border border-slate-200">
                                    <p class="border-b border-slate-100 bg-slate-50 px-3 py-2 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                                        Status penawaran relawan
                                    </p>
                                    @if (empty($offers))
                                        <p class="px-3 py-4 text-center text-[10px] text-slate-400">Belum ada penawaran terkirim.</p>
                                    @else
                                        <div class="divide-y divide-slate-100">
                                            @foreach ($offers as $offer)
                                                @php
                                                    $offerMap = [
                                                        'ditawarkan' => ['Ditawarkan', 'bg-amber-100 text-amber-700'],
                                                        'diterima' => ['Diterima', 'bg-blue-100 text-blue-700'],
                                                        'selesai' => ['Selesai', 'bg-emerald-100 text-emerald-700'],
                                                        'ditolak' => ['Ditolak', 'bg-rose-100 text-rose-700'],
                                                    ];
                                                    [$offerLabel, $offerClass] = $offerMap[$offer->status] ?? [ucfirst($offer->status), 'bg-slate-100 text-slate-600'];
                                                @endphp
                                                <div class="flex items-center justify-between gap-2 px-3 py-2">
                                                    <p class="truncate text-[11px] text-slate-700">{{ $offer->name }}</p>
                                                    <div class="flex shrink-0 items-center gap-1.5">
                                                        <span class="rounded-[3px] px-1.5 py-0.5 text-[10px] font-medium {{ $offerClass }}">{{ $offerLabel }}</span>
                                                        @if ($this->isCoordinator() && $offer->status === 'ditolak' && in_array($detailTask->status, ['terbuka', 'berjalan'], true))
                                                            <button type="button" wire:click="reoffer({{ $offer->id }})"
                                                                    class="rounded-[3px] border border-slate-300 bg-white px-1.5 py-0.5 text-[10px] font-medium text-slate-600 transition hover:bg-slate-50">
                                                                Tawar Ulang
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @if ($this->isCoordinator() && in_array($detailTask->status, ['terbuka', 'berjalan'], true))
                                    <div class="space-y-2">
                                        <button type="button" wire:click="completeTask({{ $detailTask->id }})"
                                                wire:confirm="Tandai tugas ini selesai? Relawan yang ditugaskan akan menerima notifikasi."
                                                class="w-full rounded-[4px] bg-emerald-600 py-2 text-[11px] font-medium text-white transition hover:bg-emerald-700">
                                            Selesaikan Tugas
                                        </button>
                                        @if ($detailTask->status === 'terbuka')
                                            <button type="button" wire:click="requestCancel({{ $detailTask->id }})"
                                                    class="w-full rounded-[4px] bg-rose-600 py-2 text-[11px] font-medium text-white transition hover:bg-rose-700">
                                                Batalkan Tugas
                                            </button>
                                        @endif
                                    </div>
                                @endif

                                <button type="button" wire:click="closePanel"
                                        class="w-full rounded-[4px] border border-slate-300 bg-white py-2 text-[11px] font-medium text-slate-700 transition hover:bg-slate-50">
                                    Tutup
                                </button>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="rounded-[6px] border border-dashed border-slate-200 bg-white px-4 py-8 text-center">
                        <p class="text-[11px] font-medium text-slate-600">
                            {{ $this->isCoordinator() ? 'Belum ada tugas yang dipilih' : 'Pilih tugas untuk melihat detail' }}
                        </p>
                        <p class="mt-1 text-[10px] leading-relaxed text-slate-400">
                            @if ($this->isCoordinator())
                                Tekan Buat Tugas untuk menyusun tugas baru, atau Ubah pada daftar tugas.
                            @else
                                Tekan Detail pada daftar tugas untuk melihat jadwal dan status penawaran Anda.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal alasan pembatalan --}}
    @if ($showCancelModal)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/50" wire:click="dismissCancelModal"></div>
            <div class="relative w-full max-w-sm rounded-[4px] bg-white p-4 shadow-lg">
                <h3 class="text-sm font-semibold text-slate-900">Batalkan tugas?</h3>
                <p class="mt-2 text-[11px] leading-relaxed text-slate-400">
                    Seluruh penawaran relawan pada tugas ini ikut dibatalkan dan setiap relawan menerima notifikasi
                    berisi alasan pembatalan.
                </p>

                <div class="mt-3">
                    <label class="mb-1 block text-[11px] font-semibold text-slate-700">Alasan pembatalan</label>
                    <textarea wire:model="cancelReason" rows="3" maxlength="255"
                              placeholder="Contoh: Posko sudah ditutup karena banjir surut."
                              class="w-full rounded-[4px] border border-slate-300 px-2.5 py-1.5 text-[11px] text-slate-800 focus:border-blue-500 focus:outline-none"></textarea>
                    @error('cancelReason')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" wire:click="dismissCancelModal"
                            class="rounded-[4px] border border-slate-300 bg-white px-3.5 py-1.5 text-[11px] font-medium text-slate-700 transition hover:bg-slate-50">
                        Kembali
                    </button>
                    <button type="button" wire:click="cancelTask({{ $cancelTaskId }})" wire:loading.attr="disabled" wire:target="cancelTask"
                            class="rounded-[4px] bg-rose-600 px-3.5 py-1.5 text-[11px] font-medium text-white transition hover:bg-rose-700 disabled:opacity-60">
                        Batalkan Tugas
                    </button>
                </div>
            </div>
        </div>
    @endif

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
