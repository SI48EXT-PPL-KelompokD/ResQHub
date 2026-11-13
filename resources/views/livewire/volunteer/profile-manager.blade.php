<!---- profile-manager.blade.php -->
<!DOCTYPE html>
<html lang="id" x-data="{ open: false, confirmDelete: false }" class="h-full" xmlns:xlink="http://www.w3.org/1999/xlink">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ResQHub - Profil Keahlian</title>

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-show="open" class="fixed inset-0 z-[60]" role="dialog" aria-modal="true">
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:leave="transition ease-in duration-150"
         class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="open = false" role="presentation"></div>

    <div x-show="open" class="absolute inset-y-0 right-0 w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-200 flex flex-col z-[65]">
        <!-- Header -->
        <div class="border-b border-slate-200 px-6 py-4">
            @if($mode === 'edit')
                <h2 class="text-xl font-semibold text-slate-900">Profil Keahlian</h2>
            @else
                <h2 class="text-xl font-semibold text-slate-900">Profil Saya</h2>
            @endif
            <button type="button"
                    class="absolute right-2 text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100"
                    @click="open = false"
                    aria-label="Tutup profil">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="p-6 flex-1 overflow-y-auto">
            @if($mode === 'view')
                <!-- Profil View -->
                <div class="mb-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <!-- Avatar & Nama -->
                        <div class="w-14 h-14 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold text-xs shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-800">{{ auth()->user()->name ?? 'Pengguna' }}</p>
                            <p class="text-[10px] text-slate-500">{{ Auth::user()->role_label ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Status & Kesiapan -->
                    <div class="mt-3 px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse inline-block mr-1.5"></span>
                        <span class="inline">{{ $profile ? $profile->readiness_label : 'Belum Ada Profil' }}</span>
                    </div>

                    <!-- Keahlian -->
                    @if($profile && $profile->skills->isNotEmpty())
                        <p class="mt-2 text-sm text-slate-600">Keahlian:</p>
                        <div class="mt-1 flex flex-wrap gap-1.5">
                            @foreach($profile->skills as $skill)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      style="background:{{ str_contains(strtolower($skill->name),'medis') ? 'rgba(37,99,235,0.15)' : 'rgba(107,114,128,0.1)' }};color:{{ str_contains(strtolower($skill->name),'medis') ? '#2563EB' : '#6B727E' }};"
                                      border="0">
                                    {{ $skill->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-2 text-sm text-slate-600">Belum ada keahlian terdaftar.</p>
                    @endif

                    <!-- Ringkasan -->
                    <p class="mt-3 text-sm text-slate-500">
                        Domisili {{ $profile ? auth()->user()->regency : '—' }} ·
                        Radius {{ $profile ? $profile->radius_km : '—' }} km ·
                        {{ $profile ? ($profile->certificate_path ? 'Sertifikat terunggah' : 'Belum ada sertifikat') : '—' }}
                    </p>
                </div>

                <!-- Riwayat Tugas -->
                @if($profile && ! $profile->assignments->isEmpty())
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-slate-800 mb-3">Riwayat Tugas</h3>
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            @foreach($profile->assignments as $assignment)
                                @php
                                    $statusLabels = [
                                        'selesai' => ['label' => 'Selesai', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                        'diterima' => ['label' => 'Berjalan', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                        'ditawarkan' => ['label' => 'Ditawarkan', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                        'ditolak' => ['label' => 'Ditolak', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                    ];
                                    $assignLabel = $statusLabels[$assignment->status] ?? ['label' => $assignment->status, 'class' => ''];
                                @endphp
                                <div class="flex items-center gap-3 px-2 py-1 rounded bg-slate-50">
                                    <div class="flex-1 min-w-0">
                                        <p class="truncate text-sm line-clamp-1">{{ $assignment->volunteerTask->title }}</p>
                                        <p class="text-[10px] text-slate-500 line-clamp-1">{{ \Illuminate\Support\Carbon::parse($assignment->volunteerTask->start_at)->translatedFormat('d M Y') }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{$statusLabels[$assignment->assignment_status]['class']}}">
                                        {{$statusLabels[$assignment->assignment_status]['label']}}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            @if($mode === 'edit')
                <!-- Profil Edit -->
                <!-- Keahlian -->
                <div class="mb-5">
                    <p class="text-xs text-slate-500 mb-1">Keahlian <span class="text-red-500">*</span></p>
                    <div class="space-y-1">
                        @foreach($skills as $skill)
                            <label class="cursor-pointer flex items-start space-x-2">
                                <input type="checkbox"
                                       wire:model.live="selectedSkills"
                                       value="{{ $skill->id }}"
                                       class="sr-only peer">
                                <span class="flex-1 flex items-center rounded border peer-checked:bg-blue-50 peer-checked:text-blue-700 peer-checked:border-blue-300 border-slate-200 hover:border-blue-300 transition-colors peer-disabled:cursor-not-peer peer-disabled:opacity-50">
                                    {{ $skill->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Domisili & Radius -->
                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Domisili <span class="text-red-500">*</span></p>
                        <select wire:model="regency" class="w-full rounded border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-200">
                            @foreach($regencyOptions as $opt)
                                <option value="{{ $opt }}" {{ $regency === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Radius Kesediaan (km) <span class="text-red-500">*</span></p>
                        <input type="number"
                               wire:model="radius_km"
                               min="1" max="200"
                               class="w-full rounded border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-200"
                               required>
                    </div>
                </div>

                <!-- Ketersediaan -->
                <div class="mb-5">
                    <p class="text-xs text-slate-500 mb-1">Ketersediaan <span class="text-red-500">*</span></p>
                    <div class="flex space-x-3">
                        <label>
                            <input type="radio"
                                   wire:model="availability"
                                   value="siap"
                                   class="sr-only peer">
                            <span class="peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 peer:border-slate-200 rounded border px-3 py-1 text-sm font-medium">Siap</span>
                        </label>
                        <label>
                            <input type="radio"
                                   wire:model="availability"
                                   value="tidak_siap"
                                   class="sr-only peer">
                            <span class="peer-checked:bg-red-600 peer-checked:text-white peer-checked:border-red-600 peer:border-slate-200 rounded border px-3 py-1 text-sm font-medium">Tidak Siap</span>
                        </label>
                    </div>
                </div>

                <!-- Nomor STR (Khusus Medis) -->
                @if($medicalSelected)
                    <div class="mb-5">
                        <p class="text-xs text-slate-500 mb-1">Nomor STR <span class="text-red-500">*</span></p>
                        <input type="text"
                               wire:model="str_number"
                               placeholder="STR-XXXX-XXXX-XXXX"
                               class="w-full rounded border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-200"
                               required>
                        @error('str_number')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                @endif

                <!-- Sertifikat -->
                <div class="mb-5">
                    <p class="text-xs text-slate-500 mb-1">Sertifikat Pendukung</p>
                    @if($certificate)
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-slate-600">File baru:</span>
                            <span class="text-sm text-blue-600">{{ $certificate->getClientOriginalName() }}</span>
                        </div>
                        <p class="text-xs text-emerald-600">Mengunggah... <span class="animate-pulse"></span></p>
                    @elseif($profile?->certificate_path && !$removeCertificate)
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-slate-600">Sertifikat saat ini:</span>
                            <span class="text-sm text-blue-600">{{ basename($profile->certificate_path) }}</span>
                        </div>
                        <button type="button"
                                wire:click="$set('removeCertificate', true)"
                                class="text-sm text-red-600 hover:underline">Hapus</button>
                    @else
                        <p class="text-sm text-slate-500 text-center">Belum ada sertifikat</p>
                    @endif
                    <input type="file"
                           wire:model="certificate"
                           accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full rounded border px-3 py-2 my-4 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-200">
                    @error('certificate')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            @endif

            {{-- Tombol Aksi --}}
            <div class="pt-4 border-t border-slate-200">
                @if($mode === 'view')
                    <button wire:click="edit"
                            class="w-full bg-blue-600 text-white hover:bg-blue-700 text-sm font-medium py-2.5 rounded-lg transition">
                        Ubah Profil
                    </button>
                    @if($profile && $profile->is_complete && $profile->availability === 'siap')
                        <button wire:click="confirmDelete"
                                class="w-full mt-2 bg-rose-600 text-white hover:bg-red-700 text-sm font-medium py-2.5 rounded-lg transition"
                                {{ $this->hasActiveTask() ? 'disabled' : '' }}
                                title="Profil tidak dapat dihapus selama masih ada tugas berjalan">
                            Hapus Profil
                        </button>
                    @else
                        <button wire:click="confirmDelete"
                                class="w-full mt-2 bg-rose-600 text-white hover:bg-red-700 text-sm font-medium py-2.5 rounded-lg transition"
                                disabled>
                            Hapus Profil
                        </button>
                    @endif
                @elseif($mode === 'edit')
                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="form"
                            class="w-full bg-blue-600 text-white hover:bg-blue-700 text-sm font-medium py-2.5 rounded-lg transition">
                            <svg class="inline-block animate-spin w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                            </svg>
                            Simpan Profil
                        </button>
                        <button type="button"
                                wire:click="cancel"
                                class="w-full mt-2 bg-slate-200 text-slate-700 hover:bg-slate-300 text-sm font-medium py-2.5 rounded-lg transition">
                            Batal
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[70] overflow-y-auto" @click.self="cancelDelete">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            <div class="absolute inset-4 bg-white rounded-xl p-6 w-full max-w-md mx-auto shadow-2xl">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Hapus profil relawan?</h3>
                <p class="text-sm text-slate-500 mb-6">Anda tidak akan muncul lagi di rekomendasi tugas. Riwayat tugas yang sudah selesai tetap tersimpan.</p>
                <div class="flex gap-3">
                    <button type="button"
                            wire:click="cancelDelete"
                            class="flex-1 bg-slate-100 text-slate-600 hover:bg-slate-200 px-4 py-2 rounded text-sm">
                        Batal
                    </button>
                    <button type="button"
                            wire:click="deleteProfile"
                            class="flex-1 bg-rose-600 text-white hover:bg-red-700 px-4 py-2 rounded text-sm">
                        Hapus Profil
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Notifikasi --}}
    @if($notification)
        <div class="fixed top-4 left-4 right-4 bg-{{ $notificationType === 'success' ? 'emerald-500' : 'rose-600' }} text-white text-sm px-4 py-3 rounded shadow-xl animate-in fade-in-0 z-[50]">
            <span>{{ $notification }}</span>
            <button type="button"
                    class="absolute right-2 text-opacity-90 hover:text-white"
                    @click="dismissNotification">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif
</body>
</html>