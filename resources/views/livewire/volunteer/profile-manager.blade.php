{{-- Slide-over profil keahlian relawan (PBI-19) --}}
<div>
    @if ($open)
        <div class="fixed inset-0 z-[60]" role="dialog" aria-modal="true" aria-label="Profil keahlian">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="closeProfile"></div>

            <div class="absolute inset-y-0 right-0 w-full max-w-lg bg-white shadow-2xl flex flex-col">
                {{-- Header --}}
                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Profil Keahlian</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Kelola keahlian, domisili, dan kesiapan Anda.</p>
                    </div>
                    <button type="button"
                            wire:click="closeProfile"
                            class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                            aria-label="Tutup profil">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Content --}}
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    @if ($mode === 'view')
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name ?? 'Pengguna' }}</p>
                                <p class="text-xs text-slate-500">{{ auth()->user()->role_label ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            @php
                                $readiness = $profile?->readiness_label ?? 'Belum Ada Profil';
                                $readinessClass = match ($readiness) {
                                    'Siap Digunakan' => 'bg-emerald-50 text-emerald-700',
                                    'Belum Lengkap' => 'bg-amber-50 text-amber-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $readinessClass }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ $readiness }}
                            </span>
                        </div>

                        <div class="mt-5">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Keahlian</p>
                            @if ($profile && $profile->skills->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach ($profile->skills as $skill)
                                        @php $isMedical = str_contains(strtolower($skill->name), 'medis'); @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $isMedical ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $skill->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-sm text-slate-500">Belum ada keahlian terdaftar.</p>
                            @endif
                        </div>

                        <dl class="mt-5 divide-y divide-slate-100 rounded-xl border border-slate-200">
                            <div class="flex items-center justify-between px-4 py-2.5">
                                <dt class="text-xs text-slate-500">Domisili</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ auth()->user()->regency ?: '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between px-4 py-2.5">
                                <dt class="text-xs text-slate-500">Radius kesediaan</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $profile?->radius_km ?? '—' }} km</dd>
                            </div>
                            <div class="flex items-center justify-between px-4 py-2.5">
                                <dt class="text-xs text-slate-500">Ketersediaan</dt>
                                <dd class="text-sm font-medium text-slate-800">
                                    {{ $profile?->availability === 'siap' ? 'Siap' : ($profile ? 'Tidak Siap' : '—') }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between px-4 py-2.5">
                                <dt class="text-xs text-slate-500">Nomor STR</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $profile?->str_number ?: '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between px-4 py-2.5">
                                <dt class="text-xs text-slate-500">Sertifikat</dt>
                                <dd class="text-sm font-medium text-slate-800">
                                    {{ $profile?->certificate_path ? 'Terunggah' : 'Belum ada' }}
                                </dd>
                            </div>
                        </dl>

                        @if ($taskHistory->isNotEmpty())
                            <div class="mt-5">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Riwayat Tugas</p>
                                <div class="mt-2 space-y-2">
                                    @foreach ($taskHistory as $task)
                                        @php
                                            $statusMap = [
                                                'selesai' => ['Selesai', 'bg-emerald-50 text-emerald-700'],
                                                'diterima' => ['Berjalan', 'bg-blue-50 text-blue-700'],
                                                'ditawarkan' => ['Ditawarkan', 'bg-amber-50 text-amber-700'],
                                                'ditolak' => ['Ditolak', 'bg-rose-50 text-rose-700'],
                                            ];
                                            [$statusLabel, $statusClass] = $statusMap[$task->assignment_status] ?? [ucfirst($task->assignment_status), 'bg-slate-100 text-slate-600'];
                                        @endphp
                                        <div class="flex items-center gap-3 rounded-lg bg-slate-50 px-3 py-2">
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm text-slate-800">{{ $task->title }}</p>
                                                <p class="text-[11px] text-slate-500">
                                                    {{ \Illuminate\Support\Carbon::parse($task->start_at)->translatedFormat('d M Y') }}
                                                </p>
                                            </div>
                                            <span class="shrink-0 rounded px-2 py-0.5 text-[10px] font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- Mode edit --}}
                        <div class="mb-5">
                            <p class="mb-1.5 text-xs font-medium text-slate-700">Keahlian <span class="text-rose-500">*</span></p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($skills as $skill)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" wire:model.live="selectedSkills" value="{{ $skill->id }}" class="peer sr-only">
                                        <span class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition peer-checked:border-blue-300 peer-checked:bg-blue-50 peer-checked:text-blue-700 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-200">
                                            {{ $skill->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('selectedSkills')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                        </div>

                        <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-slate-700">Domisili <span class="text-rose-500">*</span></label>
                                <select wire:model="regency" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <option value="">Pilih kabupaten/kota</option>
                                    @foreach ($regencyOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                                @error('regency')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-slate-700">Radius (km) <span class="text-rose-500">*</span></label>
                                <input type="number" wire:model="radius_km" min="1" max="200"
                                       class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                @error('radius_km')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="mb-5">
                            <p class="mb-1.5 text-xs font-medium text-slate-700">Ketersediaan <span class="text-rose-500">*</span></p>
                            <div class="inline-flex rounded-lg bg-slate-100 p-1">
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model="availability" value="siap" class="peer sr-only">
                                    <span class="inline-block rounded-md px-4 py-1.5 text-xs font-medium text-slate-500 transition peer-checked:bg-white peer-checked:text-blue-700 peer-checked:shadow-sm">Siap</span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model="availability" value="tidak_siap" class="peer sr-only">
                                    <span class="inline-block rounded-md px-4 py-1.5 text-xs font-medium text-slate-500 transition peer-checked:bg-white peer-checked:text-rose-700 peer-checked:shadow-sm">Tidak Siap</span>
                                </label>
                            </div>
                            @error('availability')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                        </div>

                        @if ($medicalSelected)
                            <div class="mb-5">
                                <label class="mb-1.5 block text-xs font-medium text-slate-700">Nomor STR <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="str_number" placeholder="STR-XXXX-XXXX-XXXX"
                                       class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                <p class="mt-1 text-[11px] text-slate-500">Wajib untuk keahlian medis.</p>
                                @error('str_number')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                            </div>
                        @endif

                        <div class="mb-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Sertifikat pendukung</label>

                            @if ($certificate)
                                <div class="flex items-center justify-between rounded-lg border border-blue-200 bg-blue-50 px-3 py-2">
                                    <span class="truncate text-xs text-blue-700">{{ $certificate->getClientOriginalName() }}</span>
                                    <span class="text-[11px] font-medium text-blue-600">Siap diunggah</span>
                                </div>
                            @elseif ($profile?->certificate_path && ! $removeCertificate)
                                <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2">
                                    <span class="truncate text-xs text-slate-600">{{ basename($profile->certificate_path) }}</span>
                                    <button type="button" wire:click="$set('removeCertificate', true)" class="text-xs font-medium text-rose-600 hover:underline">Hapus</button>
                                </div>
                            @else
                                <p class="text-xs text-slate-500">{{ $removeCertificate ? 'Sertifikat akan dihapus saat disimpan.' : 'Belum ada sertifikat.' }}</p>
                            @endif

                            <input type="file" wire:model="certificate" accept=".pdf,.jpg,.jpeg,.png"
                                   class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            <p class="mt-1 text-[11px] text-slate-500">PDF/JPG/PNG, maksimal 5 MB.</p>
                            @error('certificate')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                        </div>
                    @endif
                </div>

                {{-- Footer actions --}}
                <div class="border-t border-slate-200 px-6 py-4">
                    @if ($mode === 'view')
                        @php
                            $canDelete = $profile && $profile->is_complete && $profile->availability === 'siap' && ! $hasActiveTask;
                        @endphp
                        <button type="button" wire:click="edit"
                                class="w-full rounded-lg bg-blue-600 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">
                            {{ $profile ? 'Ubah Profil' : 'Lengkapi Profil' }}
                        </button>
                        <button type="button" wire:click="requestDelete"
                                class="mt-2 w-full rounded-lg py-2.5 text-sm font-medium transition {{ $canDelete ? 'bg-rose-600 text-white hover:bg-rose-700' : 'cursor-not-allowed bg-slate-100 text-slate-400' }}"
                                @disabled(! $canDelete)>
                            Hapus Profil
                        </button>
                        @if (! $canDelete)
                            <p class="mt-2 text-center text-[11px] text-slate-500">
                                {{ $hasActiveTask ? 'Tidak dapat dihapus: masih ada tugas berjalan.' : 'Lengkapi profil dan set ketersediaan "Siap" untuk mengaktifkan hapus.' }}
                            </p>
                        @endif
                    @else
                        <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save,certificate"
                                class="w-full rounded-lg bg-blue-600 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">Simpan Profil</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                        <button type="button" wire:click="cancel"
                                class="mt-2 w-full rounded-lg bg-slate-100 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200">
                            Batal
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Konfirmasi hapus --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="cancelDelete"></div>
            <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-semibold text-slate-900">Hapus profil relawan?</h3>
                <p class="mt-2 text-sm text-slate-500">
                    Anda tidak akan muncul lagi di rekomendasi tugas. Riwayat tugas yang sudah selesai tetap tersimpan.
                </p>
                <div class="mt-6 flex gap-3">
                    <button type="button" wire:click="cancelDelete"
                            class="flex-1 rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteProfile"
                            class="flex-1 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700">
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
