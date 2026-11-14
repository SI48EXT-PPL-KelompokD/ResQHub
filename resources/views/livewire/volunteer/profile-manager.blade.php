{{-- Modal profil keahlian relawan (PBI-19) --}}
<div>
    @if ($open)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="Profil saya">
            <div class="absolute inset-0 bg-slate-900/40" wire:click="closeProfile"></div>

            <div class="relative flex max-h-[85vh] w-full max-w-sm flex-col overflow-hidden rounded-[4px] border border-slate-200 bg-white shadow-lg">
                {{-- Header --}}
                <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3">
                    <button type="button"
                            wire:click="closeProfile"
                            class="rounded p-1 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                            aria-label="Tutup profil">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <h2 class="text-sm font-semibold text-slate-900">{{ $mode === 'edit' ? 'Profil Keahlian' : 'Profil Saya' }}</h2>
                </div>

                {{-- Content --}}
                <div class="flex-1 overflow-y-auto px-4 py-3.5">
                    @if ($mode === 'view')
                        @php
                            $readiness = $profile?->readiness_label ?? 'Belum Ada Profil';
                            $readinessClass = match ($readiness) {
                                'Siap Ditugaskan' => 'bg-emerald-50 text-emerald-700',
                                'Belum Lengkap' => 'bg-amber-50 text-amber-700',
                                default => 'bg-slate-100 text-slate-600',
                            };
                            $certificateLabel = $profile?->certificate_path ? 'sertifikat terunggah' : 'belum ada sertifikat';
                        @endphp

                        <div class="rounded-md border border-slate-200 p-4">
                            <p class="text-base font-semibold text-slate-900">{{ auth()->user()->name ?? 'Pengguna' }}</p>

                            <span class="mt-2 inline-flex items-center rounded-[4px] px-2 py-0.5 text-[11px] font-medium {{ $readinessClass }}">
                                {{ $readiness }}
                            </span>

                            @if ($profile && $profile->skills->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach ($profile->skills as $skill)
                                        <span class="inline-flex items-center rounded-[4px] border border-blue-200 bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700">
                                            {{ $skill->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-[11px] text-slate-500">Belum ada keahlian terdaftar.</p>
                            @endif

                            <p class="mt-2 text-[11px] leading-relaxed text-slate-400">
                                @php
                                    $regencyLabel = auth()->user()->regency
                                        ? str_replace('Kabupaten ', 'Kab. ', auth()->user()->regency)
                                        : '—';
                                @endphp
                                Domisili {{ $regencyLabel }} · radius {{ $profile?->radius_km ?? '—' }} km · {{ $certificateLabel }}
                            </p>
                        </div>

                        <div class="mt-3 rounded-md border border-slate-200 p-3">
                            <p class="text-sm font-semibold text-slate-900">Riwayat tugas</p>
                            @if ($taskHistory->isEmpty())
                                <p class="mt-2 text-[10px] text-slate-400">Belum ada riwayat tugas. Tugas yang Anda ambil akan muncul di sini.</p>
                            @else
                                <div class="mt-2.5 space-y-2">
                                    @foreach ($taskHistory as $task)
                                        @php
                                            $statusMap = [
                                                'selesai' => ['Selesai', 'bg-emerald-50 text-emerald-700', 'laporan dikonfirmasi'],
                                                'diterima' => ['Berjalan', 'bg-blue-50 text-blue-700', 'sedang berjalan'],
                                                'ditawarkan' => ['Ditawarkan', 'bg-amber-50 text-amber-700', 'menunggu konfirmasi'],
                                                'ditolak' => ['Ditolak', 'bg-rose-50 text-rose-700', 'ditolak'],
                                            ];
                                            [$statusLabel, $statusClass, $statusNote] = $statusMap[$task->assignment_status] ?? [ucfirst($task->assignment_status), 'bg-slate-100 text-slate-600', ''];
                                        @endphp
                                        <div class="rounded-md border border-slate-200 px-3 py-2.5">
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="text-xs font-semibold leading-snug text-slate-800">{{ $task->title }}</p>
                                                <span class="shrink-0 rounded-[4px] px-1.5 py-0.5 text-[10px] font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                                            </div>
                                            <p class="mt-1 text-[10px] text-slate-400">
                                                {{ \Illuminate\Support\Carbon::parse($task->start_at)->translatedFormat('d M Y') }}{{ $statusNote ? ' · '.$statusNote : '' }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Mode edit --}}
                        <div class="mb-3">
                            <p class="mb-1.5 text-[11px] font-semibold text-slate-700">Keahlian (pilih satu atau lebih)</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($skills as $skill)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" wire:model.live="selectedSkills" value="{{ $skill->id }}" class="peer sr-only">
                                        <span style="{{ in_array((int) $skill->id, array_map('intval', $selectedSkills), true) ? 'background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8' : '' }}"
                                              class="inline-flex items-center rounded-[4px] border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-medium text-slate-500 transition peer-checked:!border-blue-300 peer-checked:!bg-blue-50 peer-checked:!text-blue-700">
                                            {{ $skill->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('selectedSkills')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                        </div>

                        <div class="mb-3 grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-slate-700">Domisili</label>
                                <select wire:model="regency" class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <option value="">Pilih kabupaten/kota</option>
                                    @foreach ($regencyOptions as $option)
                                        <option value="{{ $option }}">{{ str_replace('Kabupaten ', 'Kab. ', $option) }}</option>
                                    @endforeach
                                </select>
                                @error('regency')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-slate-700">Radius kesediaan</label>
                                <div class="relative">
                                    <input type="number" wire:model="radius_km" min="1" max="200"
                                           class="w-full rounded-[4px] border border-slate-200 bg-white py-1.5 pl-2.5 pr-8 text-xs text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <span class="pointer-events-none absolute inset-y-0 right-2.5 flex items-center text-[10px] text-slate-400">km</span>
                                </div>
                                @error('radius_km')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="mb-1 block text-[11px] font-semibold text-slate-700">Ketersediaan</label>
                            <select wire:model="availability" class="w-full rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                <option value="siap">Siap</option>
                                <option value="tidak_siap">Tidak Siap</option>
                            </select>
                            @error('availability')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="mb-1 block text-[11px] font-semibold text-slate-700">Nomor STR (wajib untuk keahlian medis)</label>
                            <input type="text" wire:model="str_number" placeholder="{{ $medicalSelected ? 'STR-XXXX-XXXX-XXXX' : '-' }}"
                                   @disabled(! $medicalSelected)
                                   class="w-full rounded-[4px] border px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-100 {{ $medicalSelected ? 'border-slate-200 bg-white text-slate-800 focus:border-blue-400' : 'cursor-not-allowed border-slate-200 bg-white text-slate-300' }}">
                            @error('str_number')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                        </div>

                        <div class="mb-1">
                            <label class="mb-1 block text-[11px] font-semibold text-slate-700">Sertifikat (opsional)</label>
                            @php
                                $certificateName = $certificate?->getClientOriginalName()
                                    ?? (($profile?->certificate_path && ! $removeCertificate) ? basename($profile->certificate_path) : null);
                            @endphp
                            <div class="flex items-center gap-2">
                                <label class="relative flex min-w-0 flex-1 cursor-pointer items-center rounded-[4px] border border-slate-200 bg-white px-2.5 py-1.5 transition hover:bg-slate-50">
                                    <span class="truncate text-[11px] {{ $certificateName ? 'text-slate-500' : 'text-slate-300' }}">
                                        {{ $certificateName ?? 'Belum ada sertifikat' }}
                                    </span>
                                    <input type="file" wire:model="certificate" accept=".pdf,.jpg,.jpeg,.png" class="sr-only">
                                </label>
                                @if ($certificateName)
                                    <button type="button" wire:click="$set('removeCertificate', true)"
                                            class="shrink-0 text-[10px] font-medium text-rose-600 hover:underline">
                                        Hapus
                                    </button>
                                @endif
                            </div>
                            <p class="mt-1 text-[10px] text-slate-400" wire:loading wire:target="certificate">Mengunggah sertifikat...</p>
                            @error('certificate')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                        </div>

                        <p class="mt-3 text-[11px] leading-relaxed text-slate-500">
                            Profil lengkap berstatus Siap Ditugaskan dan muncul di rekomendasi koordinator.
                        </p>
                    @endif
                </div>

                {{-- Footer actions --}}
                <div class="border-t border-slate-200 px-4 py-3.5">
                    @if ($mode === 'view')
                        @php
                            $canDelete = $profile && $profile->is_complete && $profile->availability === 'siap' && ! $hasActiveTask;
                        @endphp
                        <div class="grid grid-cols-2 gap-2.5">
                            <button type="button" wire:click="edit"
                                    class="rounded-[4px] border border-slate-300 bg-white py-2 text-xs font-medium text-slate-800 transition hover:bg-slate-50">
                                Ubah Profil
                            </button>
                            <button type="button" wire:click="requestDelete"
                                    class="rounded-[4px] py-2 text-xs font-medium transition {{ $canDelete ? 'bg-rose-600 text-white hover:bg-rose-700' : 'cursor-not-allowed bg-slate-100 text-slate-400' }}"
                                    @disabled(! $canDelete)>
                                Hapus Profil
                            </button>
                        </div>
                        <p class="mt-2.5 text-center text-[10px] text-slate-400">
                            Profil tidak dapat dihapus selama masih ada tugas berjalan.
                        </p>
                    @else
                        <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save,certificate"
                                class="w-full rounded-[4px] bg-blue-600 py-2 text-xs font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">Simpan Profil</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Konfirmasi hapus --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/50" wire:click="cancelDelete"></div>
            <div class="relative w-full max-w-sm rounded-[4px] bg-white p-4 shadow-lg">
                <h3 class="text-sm font-semibold text-slate-900">Hapus profil relawan?</h3>
                <p class="mt-2 text-[11px] leading-relaxed text-slate-400">
                    Anda tidak akan muncul lagi di rekomendasi tugas. Riwayat tugas yang sudah selesai tetap tersimpan.
                </p>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" wire:click="cancelDelete"
                            class="rounded-[4px] border border-slate-300 bg-white px-3.5 py-1.5 text-[11px] font-medium text-slate-700 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteProfile"
                            class="rounded-[4px] bg-rose-600 px-3.5 py-1.5 text-[11px] font-medium text-white transition hover:bg-rose-700">
                        Hapus Profil
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
